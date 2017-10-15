<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI;

use PHPComponent\DI\Extensions\Extension;
use PHPComponent\DI\Reference\IMethodReference;
use PHPComponent\DI\Reference\IServiceReference;
use PHPComponent\PhpCodeGenerator\AttributeFragment;
use PHPComponent\PhpCodeGenerator\ClassFragment;
use PHPComponent\PhpCodeGenerator\CodeFormatter;
use PHPComponent\PhpCodeGenerator\MethodFragment;
use PHPComponent\PhpCodeGenerator\ParameterFragment;
use PHPComponent\PhpCodeGenerator\PhpCodeFragment;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class Compiler
{

    const META_SERVICES = 'services';
    const META_METHODS = 'methods';

    /** @var IContainerBuilder */
    private $container_builder;

    /** @var array */
    private $meta_data = array();

    /** @var CodeFormatter */
    private $code_formatter;

    /** @var Extension[] */
    private $extensions = array();

    /**
     * Compiler constructor.
     * @param IContainerBuilder $container_builder
     * @param CodeFormatter $code_formatter
     */
    public function __construct(IContainerBuilder $container_builder, CodeFormatter $code_formatter)
    {
        $this->container_builder = $container_builder;
        $this->code_formatter = $code_formatter;
    }

    /**
     * @param string $class_name
     * @return PhpCodeFragment
     */
    public function compile($class_name)
    {
        foreach($this->getExtensions() as $extension)
        {
            $extension->beforeCompile();
        }

        $php_code = new PhpCodeFragment();
        $container_fragment = new ClassFragment($class_name);
        $container_fragment->setExtends(Container::class);

        $parameters_attribute = new AttributeFragment('parameters');
        $parameters_attribute->setDefaultValue($this->getContainerBuilder()->getParameters());
        $container_fragment->addAttribute($parameters_attribute);

        foreach($this->getContainerBuilder()->getServicesDefinitions() as $service_key => $service_definition)
        {
            $this->meta_data[self::META_SERVICES][$service_key] = $service_definition->getClassName();
            $method_name = $this->generateCamelCase($service_key);
            $this->meta_data[self::META_METHODS][$service_key] = $method_name;
            $method_fragment = $this->generateServiceMethod($service_key, $service_definition);
            $container_fragment->addMethod($method_fragment);
        }

        $meta_data_attribute = new AttributeFragment('meta_data');
        $meta_data_attribute->setDefaultValue($this->meta_data);
        $container_fragment->addAttribute($meta_data_attribute);

        $container_fragment->addMethod($this->generateGetServiceByClassName());
        $container_fragment->addMethod($this->generateGetServiceKeyByClassName());
        $container_fragment->addMethod($this->generateGetServiceMethod());
        $container_fragment->addMethod($this->generateGetParameter());
        $container_fragment->addMethod($this->generateConstructor());

        $php_code->addCodeFragment($container_fragment);
        return $php_code;
    }

    /**
     * @return MethodFragment
     */
    private function generateConstructor()
    {
        $method_fragment = new MethodFragment('__construct');
        return $method_fragment;
    }

    /**
     * @return MethodFragment
     */
    private function generateGetServiceByClassName()
    {
        $method_fragment = new MethodFragment('getServiceByClassName');
        $method_fragment->addParameter(new ParameterFragment('class_name'));
        $body = <<<'BODY'
        $service = parent::getServiceByClassName($class_name);
        if($service !== null) return $service;
        
        foreach($this->meta_data['services'] as $service_key => $service)
        {
            if(PHPComponent\DI\Helpers::isClassTypeOf($service, $class_name))
            {
                return $this->getService($service_key);
            }
        }
        return null;
BODY;
        $method_fragment->setBody($body);
        return $method_fragment;
    }

    /**
     * @return MethodFragment
     */
    private function generateGetParameter()
    {
        $method_fragment = new MethodFragment('getParameter');
        $method_fragment->addParameter(new ParameterFragment('key'));
        $body = <<<'BODY'
        if(array_key_exists($key, $this->parameters))
        {
            return $this->parameters[$key];
        }
        return null;
BODY;
        $method_fragment->setDocComment("@inheritdoc");
        $method_fragment->setBody($body);
        return $method_fragment;
    }

    /**
     * @return MethodFragment
     */
    private function generateGetServiceKeyByClassName()
    {
        $method_fragment = new MethodFragment('getServiceKeyByClassName');
        $method_fragment->addParameter(new ParameterFragment('class_name'));
        $body = <<<'BODY'
        $service_key = parent::getServiceKeyByClassName($class_name);
        if($service_key !== null) return $service_key;
        
        foreach($this->meta_data['services'] as $service_key => $service)
        {
            if(PHPComponent\DI\Helpers::isClassTypeOf($service, $class_name))
            {
                return $this->getService($service_key);
            }
        }
        return null;
BODY;
        $method_fragment->setBody($body);
        return $method_fragment;
    }

    /**
     * @return MethodFragment
     */
    private function generateGetServiceMethod()
    {
        $method_fragment = new MethodFragment('getService');
        $method_fragment->addParameter(new ParameterFragment('service_key'));
        $method_fragment->setDocComment("@param string \$service_key \n @return object");
        $body = 'if($this->hasService($service_key)) return parent::getService($service_key);';
        $body .= 'if(array_key_exists($service_key, $this->meta_data[\'methods\'])){$method_name = \'get\'.$this->meta_data[\'methods\'][$service_key].\'Service\';return $this->$method_name();}';
        $body .= 'return null;';
        $method_fragment->setBody($body);
        return $method_fragment;
    }

    /**
     * @param $service_key
     * @param IServiceDefinition $service_definition
     * @return MethodFragment
     */
    private function generateServiceMethod($service_key, $service_definition)
    {
        $method_fragment = new MethodFragment('get'.$this->generateCamelCase($service_key).'Service');

        $body = '';
        if($service_definition->isShared())
        {
            $body .= 'if(!$this->hasService(\''.$service_key.'\'))'."\n{";
        }
        if($service_definition->hasFactoryMethod())
        {
            $arguments = $service_definition->getArguments();
            $factory_method = $service_definition->getFactoryMethod();
            if(is_string($factory_method))
            {
                $body .= '$service = '.$factory_method.'('.implode(', ', $this->resolveArguments($arguments)).');';
            }
            elseif(is_array($factory_method))
            {
                $class = $factory_method[0];
                $method = $factory_method[1];
                if(is_string($class))
                {
                    $body .= '$service = '.$class.'::'.$method.'('.implode(', ', $this->resolveArguments($arguments)).');';
                }
                else
                {
                    $body .= '$service = '.$this->resolveParameterReference($class).'->'.$method.'('.implode(', ', $this->resolveArguments($arguments)).');';
                }
            }
            elseif($factory_method instanceof IMethodReference)
            {
                $body .= '$service = $this->getService(\''.$factory_method->getServiceReference()->getServiceKey().'\')->'.$factory_method->getMethodName().'('.implode(', ', $this->resolveArguments($factory_method->getArguments())).');';
            }
        }
        else
        {
            $reflection_class = new \ReflectionClass($service_definition->getClassName());
            if($reflection_class->getConstructor() === null)
            {
                $arguments = $service_definition->getArguments();
            }
            else
            {
                $reflection_constructor = $reflection_class->getConstructor();
                $arguments = Helpers::autoWireArguments($reflection_constructor, $service_definition->getArguments(), $this->getContainerBuilder(), Helpers::OBJECTS_AS_SERVICE_REFERENCE);
            }
            $body .= '$service = new '.$service_definition->getClassName().'('.implode(', ', $this->resolveArguments($arguments)).');';
        }

        if($service_definition->isShared())
        {
            $body .= 'if(!$this->hasService(\''.$service_key.'\')){'."\n";
            $body .= '$this->addService(\''.$service_key.'\', $service);';
            $body .= "\n}";
        }

        if($service_definition->hasPropertiesSetters())
        {
            foreach($service_definition->getPropertiesSetters() as $property_setter)
            {
                $body .= '$service->'.$property_setter->getPropertyName().' = '.$this->resolveParameterReference($property_setter->getValue()).';';
            }
        }

        if($service_definition->hasMethodsCalls())
        {
            foreach($service_definition->getMethodsCalls() as $method_call)
            {
                if($method_call->getService() === null)
                {
                    $body .= '$service->';
                }
                elseif(is_string($method_call->getService()))
                {
                    $body .= $method_call->getService().'::';
                }
                elseif($method_call->getService() instanceof IServiceReference)
                {
                    $body .= '$this->get'.$this->meta_data[self::META_METHODS][$method_call->getService()->getServiceKey()].'Service()->';
                }
                $body .= $method_call->getMethodName().'('.implode(', ', $this->resolveArguments($method_call->getArguments())).');';
            }
        }

        if($service_definition->isShared())
        {
            $body .= "\n}";
            $body .= 'return parent::getService(\''.$service_key.'\');';
        }
        else
        {
            $body .= 'return $service;';
        }
        $method_fragment->setDocComment('@return '.$service_definition->getClassName());
        $method_fragment->setBody($body);
        return $method_fragment;
    }

    /**
     * @param array $arguments
     * @return array
     */
    private function resolveArguments($arguments)
    {
        $data = array();
        foreach($arguments as $argument)
        {
            $data[] = $this->resolveParameterReference($argument);
        }
        return $data;
    }

    /**
     * @param mixed $parameter
     * @return mixed
     */
    private function resolveParameterReference($parameter)
    {
        if($parameter instanceof IServiceReference)
        {
            if(!array_key_exists($parameter->getServiceKey(), $this->meta_data[self::META_METHODS]))
            {
                $this->meta_data[self::META_METHODS][$parameter->getServiceKey()] = $this->generateCamelCase($parameter->getServiceKey());
            }
            $service_name = $this->meta_data[self::META_METHODS][$parameter->getServiceKey()];
            $parameter = '$this->get'.$service_name.'Service()';
        }
        elseif($parameter instanceof IMethodReference)
        {
            if(!array_key_exists($parameter->getServiceReference()->getServiceKey(), $this->meta_data[self::META_METHODS]))
            {
                $this->meta_data[self::META_METHODS][$parameter->getServiceReference()->getServiceKey()] = $this->generateCamelCase($parameter->getServiceReference()->getServiceKey());
            }
            $service_name = $this->meta_data[self::META_METHODS][$parameter->getServiceReference()->getServiceKey()];
            $parameter = '$this->get'.$service_name.'Service()->'.$parameter->getMethodName().'('.implode(', ', $this->resolveArguments($parameter->getArguments())).')';
        }
        elseif($parameter instanceof IContainer)
        {
            $parameter = '$this';
        }
        elseif(is_array($parameter))
        {
            $is_callable = is_callable($parameter, true);
            foreach($parameter as $key => $value)
            {
                $parameter[$key] = $this->resolveParameterReference($value);
            }
            $parameter = $this->getCodeFormatter()->printValue($parameter, $is_callable);
        }
        elseif($this->getContainerBuilder()->isParameter($parameter, $attribute_name))
        {
            $parameter = '$this->parameters[\''.$attribute_name.'\']';
        }
        elseif($this->getContainerBuilder()->containsParameter($parameter, $normalized_parameter, $before_parameter, $resolved_parameter, $after_parameter))
        {
            $parameter = null;
            if($before_parameter !== null) $parameter = $this->getCodeFormatter()->printValue($before_parameter).'.';
            $parameter .= '$this->parameters[\''.$normalized_parameter.'\']';
            if($after_parameter !== null) $parameter .= '.'.$this->getCodeFormatter()->printValue($after_parameter);
        }
        else
        {
            $parameter = $this->getCodeFormatter()->printValue($parameter);
        }
        return $parameter;
    }

    /**
     * @param string $name
     * @return string
     */
    private function generateCamelCase($name)
    {
        $name = preg_replace_callback('#(?:([a-z])[^a-z]([a-z]))#i', function($matches)
        {
            return $matches[1].strtoupper($matches[2]);
        }, $name);
        $name = ucfirst($name);
        return $name;
    }

    /**
     * @param Extension $extension
     * @return $this
     */
    public function addExtension(Extension $extension)
    {
        $extension_key = strtolower($extension->getExtensionName());
        if(array_key_exists($extension_key, $this->extensions))
        {
            throw new \InvalidArgumentException('Extension with name '.$extension_key.' is already registered');
        }
        $this->extensions[$extension_key] = $extension->setCompiler($this);
        return $this;
    }

    /**
     * @param string $extension_name
     * @return null|Extension
     */
    public function getExtension($extension_name)
    {
        $extension_key = strtolower($extension_name);
        if(array_key_exists($extension_key, $this->extensions)) return $this->extensions[$extension_key];
        return null;
    }

    /**
     * @return Extension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param string $extension_name
     * @return bool
     */
    public function hasExtension($extension_name)
    {
        $extension_key = strtolower($extension_name);
        return array_key_exists($extension_key, $this->extensions);
    }

    /**
     * @return CodeFormatter
     */
    public function getCodeFormatter()
    {
        return $this->code_formatter;
    }

    /**
     * @return IContainerBuilder
     */
    public function getContainerBuilder()
    {
        return $this->container_builder;
    }
}
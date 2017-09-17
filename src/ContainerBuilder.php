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

use PHPComponent\DI\Exceptions\InvalidFactoryMethodException;
use PHPComponent\DI\Exceptions\NonExistentClassException;
use PHPComponent\DI\Exceptions\NonExistentPropertyException;
use PHPComponent\DI\Exceptions\NonExistentServiceException;
use PHPComponent\DI\Exceptions\PropertyNotPublicException;
use PHPComponent\DI\Exceptions\ServiceAlreadyExistsException;
use PHPComponent\DI\Exceptions\ServiceDefinitionAlreadyExistsException;
use PHPComponent\DI\Reference\IMethodReference;
use PHPComponent\DI\Reference\IServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ContainerBuilder extends Container implements IContainerBuilder
{

    /** @var IServiceDefinition[] */
    private $services_definitions = array();

    /**
     * @param string $key
     * @param string $service_name
     * @return IServiceDefinition
     * @throws ServiceDefinitionAlreadyExistsException
     */
    public function registerService($key, $service_name)
    {
        $key = $this->formatKey($key);
        return $this->addServiceDefinition($key, new ServiceDefinition($service_name));
    }

    /**
     * @param string $key
     * @return null|object
     * @throws InvalidFactoryMethodException
     * @throws NonExistentClassException
     * @throws NonExistentServiceException
     */
    public function getService($key)
    {
        $key = $this->formatKey($key);
        if(!$this->hasServiceDefinition($key)) throw new NonExistentServiceException('Non-existent Service Definition '.$key);
        if($this->getServiceDefinition($key)->isShared() && parent::hasService($key)) return parent::getService($key);
        return $this->createService($key, $this->getServiceDefinition($key));
    }

    /**
     * @param string $key
     * @param IServiceDefinition $definition
     * @return object
     * @throws InvalidFactoryMethodException
     * @throws NonExistentClassException
     * @throws NonExistentServiceException
     */
    private function createService($key, IServiceDefinition $definition)
    {
        $class_name = $this->resolveParameter($definition->getClassName());
        if(!class_exists($class_name)) throw new NonExistentClassException('Non-existent class '.$class_name);
        $reflection = new \ReflectionClass($class_name);

        $arguments = $this->resolveParameters($definition->getArguments());

        if($definition->hasFactoryMethod())
        {
            $factory_method = $definition->getFactoryMethod();
            if(is_string($factory_method))
            {
                $service = call_user_func_array(array($class_name, $factory_method), $arguments);
            }
            elseif(is_array($factory_method))
            {
                $service = call_user_func_array(array($this->resolveServiceReference($factory_method[0]), $factory_method[1]), $arguments);
            }
            elseif($factory_method instanceof IMethodReference)
            {
                $service = $this->resolveMethodReference($factory_method);
            }
            else
            {
                throw new InvalidFactoryMethodException('Cannot create service '.$key.' because of invalid factory method');
            }
        }
        else
        {
            $service_constructor = $reflection->getConstructor();
            if($service_constructor === null || $service_constructor->getNumberOfParameters() === 0)
            {
                $service = $reflection->newInstance();
            }
            elseif($service_constructor->getNumberOfParameters() === count($arguments))
            {
                $service = $reflection->newInstanceArgs($arguments);
            }
            else
            {
                $parameters = Helpers::autoWireArguments($service_constructor, $arguments, $this, Helpers::OBJECTS_AS_SERVICE_REFERENCE);
                $parameters = $this->resolveParameters($parameters);
                $service = $reflection->newInstanceArgs($parameters);
            }
        }

        if($definition->isShared())
        {
            if(!$this->hasService($key))
            {
                $service = $this->shareService($key, $service);
            }
            else
            {
                return $this->getService($key);
            }
        }

        if($definition->hasPropertiesSetters())
        {
            foreach($definition->getPropertiesSetters() as $property_setter)
            {
                $this->setProperty($service, $property_setter);
            }
        }

        if($definition->hasMethodsCalls())
        {
            foreach($definition->getMethodsCalls() as $method_call)
            {
                $this->callMethod($service, $method_call);
            }
        }

        return $service;
    }

    /**
     * @param string $key
     * @param IServiceDefinition $definition
     * @return IServiceDefinition
     * @throws ServiceDefinitionAlreadyExistsException
     */
    public function addServiceDefinition($key, IServiceDefinition $definition)
    {
        $key = $this->formatKey($key);
        if($this->hasServiceDefinition($key)) 
            throw new ServiceDefinitionAlreadyExistsException('Service Definition '.$key.' already exists');
        return $this->services_definitions[$key] = $definition;
    }

    /**
     * @param IServiceDefinition[] $services_definitions
     * @throws ServiceDefinitionAlreadyExistsException
     */
    public function setServicesDefinitions(array $services_definitions)
    {
        $this->services_definitions = array();
        foreach($services_definitions as $key => $service_definition)
        {
            $this->addServiceDefinition($key, $service_definition);
        }
    }

    /**
     * @param string $key
     * @return null|IServiceDefinition
     */
    public function getServiceDefinition($key)
    {
        $key = $this->formatKey($key);
        if($this->hasServiceDefinition($key)) return $this->services_definitions[$key];
        return null;
    }

    /**
     * @return IServiceDefinition[]
     */
    public function getServicesDefinitions()
    {
        return $this->services_definitions;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasServiceDefinition($key)
    {
        $key = $this->formatKey($key);
        return array_key_exists($key, $this->services_definitions);
    }

    /**
     * @param string $class_name
     * @return null|object
     * @throws InvalidFactoryMethodException
     * @throws NonExistentClassException
     * @throws NonExistentServiceException
     */
    public function getServiceByClassName($class_name)
    {
        if(!class_exists($class_name)) throw new NonExistentClassException('Non-existent class '.$class_name);

        $service = parent::getServiceByClassName($class_name);
        if($service !== null) return $service;

        foreach($this->getServicesDefinitions() as $key => $service_definition)
        {
            $definition_class_name = $this->resolveParameter($service_definition->getClassName());
            if(Helpers::isClassTypeOf($definition_class_name, $class_name)) return $this->createService($key, $service_definition);
        }

        return null;
    }

    /**
     * @param string $class_name
     * @return null|string
     * @throws NonExistentClassException
     */
    public function getServiceKeyByClassName($class_name)
    {
        if(!class_exists($class_name)) throw new NonExistentClassException('Non-existent class '.$class_name);

        $service_key = parent::getServiceKeyByClassName($class_name);
        if($service_key !== null) return $service_key;

        foreach($this->getServicesDefinitions() as $key => $service_definition)
        {
            $definition_class_name = $this->resolveParameter($service_definition->getClassName());
            if(Helpers::isClassTypeOf($definition_class_name, $class_name)) return $key;
        }

        return null;
    }

    /**
     * @param string $key
     * @param object $service
     * @return object
     * @throws \InvalidArgumentException
     * @throws ServiceAlreadyExistsException
     */
    private function shareService($key, $service)
    {
        $this->addService($key, $service);
        return parent::getService($key);
    }

    /**
     * @param object $service
     * @param IMethodCall $method_call
     * @return void
     */
    private function callMethod($service, IMethodCall $method_call)
    {
        $parsed_arguments = $this->resolveParameters($method_call->getArguments());
        $parsed_method_name = $this->resolveParameter($method_call->getMethodName());
        if($method_call->getService() instanceof IServiceReference)
        {
            $service = $this->resolveServiceReference($method_call->getService());
        }
        elseif(is_string($method_call->getService()) || is_object($method_call->getService()))
        {
            $service = $method_call->getService();
        }
        call_user_func_array(array($service, $parsed_method_name), $parsed_arguments);
    }

    /**
     * @param object $service
     * @param IPropertySetter $property_setter
     * @throws NonExistentPropertyException
     */
    private function setProperty($service, IPropertySetter $property_setter)
    {
        $parsed_value = $this->resolveParameters($property_setter->getValue());
        $parsed_property_name = $this->resolveParameter($property_setter->getPropertyName());

        $reflection_class = new \ReflectionClass($service);
        if(!$reflection_class->hasProperty($parsed_property_name))
        {
            throw new NonExistentPropertyException('Class '.$reflection_class->getName().' does not have property '.$parsed_property_name);
        }
        $reflection_property = $reflection_class->getProperty($parsed_property_name);
        if(!$reflection_property->isPublic())
        {
            throw new PropertyNotPublicException('Property '.$parsed_property_name.' in class '.$reflection_class->getName().' is not public');
        }
        $reflection_property->setValue($service, $parsed_value);
    }

    /**
     * @param mixed $parameter
     * @return mixed
     * @throws NonExistentServiceException
     */
    private function resolveParameters($parameter)
    {
        return $this->resolveMethodReference($this->resolveServiceReference($this->resolveArgumentCallback($this->resolveParameter($parameter))));
    }

    /**
     * @param mixed $argument
     * @return array|mixed
     * @throws InvalidFactoryMethodException
     * @throws NonExistentClassException
     * @throws NonExistentServiceException
     */
    private function resolveMethodReference($argument)
    {
        if(is_array($argument))
        {
            $arguments = array();
            foreach($argument as $key => $value)
            {
                $arguments[$key] = $this->resolveMethodReference($value);
            }
            return $arguments;
        }

        if($argument instanceof IMethodReference)
        {
            $service_key = $argument->getServiceReference()->getServiceKey();
            $method = $this->resolveParameter($argument->getMethodName());
            $parameters = $argument->getArguments();

            if($this->hasService($service_key))
            {
                return call_user_func_array(array(parent::getService($service_key), $method), $parameters);
            }
            elseif($this->hasServiceDefinition($service_key))
            {
                $service = $this->createService($service_key, $this->getServiceDefinition($service_key));
                return call_user_func_array(array($service, $method), $parameters);
            }
            else
            {
                throw new NonExistentServiceException('Service Definition '.$service_key.' does not exists');
            }
        }
        return $argument;
    }

    /**
     * @param mixed $argument
     * @return mixed
     * @throws InvalidFactoryMethodException
     * @throws NonExistentClassException
     * @throws NonExistentServiceException
     */
    private function resolveServiceReference($argument)
    {
        if(is_array($argument))
        {
            $arguments = array();
            foreach($argument as $key => $value)
            {
                $arguments[$key] = $this->resolveServiceReference($value);
            }
            return $arguments;
        }

        if($argument instanceof IServiceReference)
        {
            $service_key = $argument->getServiceKey();
            if($this->hasService($service_key))
            {
                return parent::getService($service_key);
            }
            elseif($this->hasServiceDefinition($service_key))
            {
                return $this->createService($service_key, $this->getServiceDefinition($service_key));
            }
            else
            {
                throw new NonExistentServiceException('Service Definition '.$service_key.' does not exists');
            }
        }

        return $argument;
    }

    /**
     * @param mixed $argument
     * @return mixed
     */
    private function resolveArgumentCallback($argument)
    {
        if(is_array($argument))
        {
            $arguments = array();
            foreach($argument as $key => $value)
            {
                $arguments[$key] = $this->resolveArgumentCallback($value);
            }
            return $arguments;
        }

        if($argument instanceof IArgumentCallback)
        {
            return $this->resolveParameter(call_user_func_array($argument->getCallback(), array()));
        }

        return $argument;
    }

    /**
     * @param mixed $argument
     * @return mixed|null|string
     */
    public function resolveParameter($argument)
    {
        if(is_array($argument))
        {
            $arguments = array();
            foreach($argument as $key => $value)
            {
                $arguments[parent::resolveParameter($key)] = parent::resolveParameter($value);
            }
            return $arguments;
        }

        if(!is_string($argument)) return $argument;

        return parent::resolveParameter($argument);
    }
}
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

use PHPComponent\DI\Reference\IMethodReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ServiceDefinition implements IServiceDefinition
{

    /** @var string */
    private $class_name;

    /** @var null|string|array|IMethodReference */
    private $factory_method;

    /** @var array */
    private $arguments = array();

    /** @var bool */
    private $shared = true;

    /** @var IMethodCall[] */
    private $methods_calls = array();

    /** @var IPropertySetter[] */
    private $properties_setters = array();

    /**
     * ServiceDefinition constructor.
     * @param string $class_name
     */
    public function __construct($class_name)
    {
        $this->setClassName($class_name);
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array();
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function addArguments(array $arguments)
    {
        foreach($arguments as $argument)
        {
            $this->arguments[] = $argument;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param IPropertySetter[] $properties_setters
     * @return $this
     */
    public function setPropertiesSetters(array $properties_setters)
    {
        $this->properties_setters = array();
        foreach($properties_setters as $property_setter)
        {
            $this->addPropertySetter($property_setter);
        }

        return $this;
    }

    /**
     * @param IPropertySetter $property_setter
     * @return $this
     */
    public function addPropertySetter(IPropertySetter $property_setter)
    {
        $this->properties_setters[] = $property_setter;
        return $this;
    }

    /**
     * @return IPropertySetter[]
     */
    public function getPropertiesSetters()
    {
        return $this->properties_setters;
    }

    /**
     * @return bool
     */
    public function hasPropertiesSetters()
    {
        return count($this->getPropertiesSetters()) > 0;
    }

    /**
     * @param IMethodCall[] $methods_calls
     * @return $this
     */
    public function setMethodsCalls(array $methods_calls)
    {
        $this->methods_calls = array();
        foreach($methods_calls as $method_call)
        {
            $this->addMethodCall($method_call);
        }
        return $this;
    }

    /**
     * @param IMethodCall $method_call
     * @return $this
     */
    public function addMethodCall(IMethodCall $method_call)
    {
        $this->methods_calls[] = $method_call;
        return $this;
    }

    /**
     * @return IMethodCall[]
     */
    public function getMethodsCalls()
    {
        return $this->methods_calls;
    }

    /**
     * @return bool
     */
    public function hasMethodsCalls()
    {
        return count($this->getMethodsCalls()) > 0;
    }

    /**
     * @param null|string|array|IMethodReference $factory_method
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFactoryMethod($factory_method)
    {
        if(is_string($factory_method) && stripos($factory_method, "::") !== false)
        {
            $factory_method = explode("::", $factory_method);
            $this->factory_method = $factory_method;
        }
        elseif(is_string($factory_method))
        {
            $this->factory_method = $factory_method;
        }
        elseif(is_array($factory_method) && count($factory_method) === 2)
        {
            $this->factory_method = $factory_method;
        }
        elseif($factory_method instanceof IMethodReference)
        {
            $this->factory_method = $factory_method;
        }
        elseif($factory_method === null)
        {
            $this->factory_method = $factory_method;
        }
        else
        {
            throw new \InvalidArgumentException('Invalid factory method');
        }

        return $this;
    }

    /**
     * @return null|string|array|IMethodReference
     */
    public function getFactoryMethod()
    {
        return $this->factory_method;
    }

    /**
     * @return bool
     */
    public function hasFactoryMethod()
    {
        return $this->getFactoryMethod() !== null;
    }

    /**
     * @param bool $shared
     * @return $this
     */
    public function setShared($shared)
    {
        if(!is_bool($shared)) throw new \InvalidArgumentException('Argument $shared must be bool instead of '.gettype($shared));
        $this->shared = $shared;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflection()
    {
        return new \ReflectionClass($this->getClassName());
    }

    /**
     * @param string $class_name
     * @throws \InvalidArgumentException
     */
    private function setClassName($class_name)
    {
        if(!is_string($class_name) || ($class_name = trim($class_name)) === '')
            throw new \InvalidArgumentException('Argument $class_name must be non-empty string instead of '.gettype($class_name));
        $this->class_name = $class_name;
    }
}
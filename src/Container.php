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

use PHPComponent\DI\Exceptions\ParameterAlreadyExistsException;
use PHPComponent\DI\Exceptions\ServiceAlreadyExistsException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class Container implements IContainer
{

    /** @var ParametersBag */
    private $parameters;

    /** @var object[] */
    private $services = array();

    /**
     * Container constructor.
     * @param ParametersBag $parameters
     */
    public function __construct(ParametersBag $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $key
     * @param object $service
     * @return $this
     * @throws \InvalidArgumentException
     * @throws ServiceAlreadyExistsException
     */
    public function addService($key, $service)
    {
        $key = $this->formatKey($key);
        if($this->hasService($key))
            throw new ServiceAlreadyExistsException('Service '.$key.' already exists');
        if(!is_object($service) || $service instanceof \Closure)
            throw new \InvalidArgumentException('Argument $service must be object instead of '.gettype($service));
        $this->services[$key] = $service;
        return $this;
    }

    /**
     * @param string $key
     * @return object|null
     */
    public function getService($key)
    {
        $key = $this->formatKey($key);
        if($this->hasService($key)) return $this->services[$key];
        return null;
    }

    /**
     * @return object[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasService($key)
    {
        $key = $this->formatKey($key);
        return array_key_exists($key, $this->services);
    }

    /**
     * @param string $class_name
     * @return null|object
     */
    public function getServiceByClassName($class_name)
    {
        foreach($this->getServices() as $service)
        {
            if(Helpers::isClassTypeOf($service, $class_name)) return $service;
        }
        return null;
    }

    /**
     * @param string $class_name
     * @return null|string
     */
    public function getServiceKeyByClassName($class_name)
    {
        foreach($this->getServices() as $service_key => $service)
        {
            if(Helpers::isClassTypeOf($service, $class_name)) return $service_key;
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $parameter
     * @return $this
     * @throws ParameterAlreadyExistsException
     */
    public function addParameter($key, $parameter)
    {
        $this->getParametersBag()->addParameter($key, $parameter);
        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     * @throws ParameterAlreadyExistsException
     */
    public function addParameters(array $parameters)
    {
        $this->getParametersBag()->addParameters($parameters);
        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->getParametersBag()->setParameters($parameters);
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getParameter($key)
    {
        return $this->getParametersBag()->getParameter($key);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->getParametersBag()->getParameters();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter($key)
    {
        return $this->getParametersBag()->hasParameter($key);
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    public function resolveParameter($parameter)
    {
        return $this->getParametersBag()->resolveParameter($parameter);
    }

    /**
     * @param string $parameter
     * @param null|string $normalized_parameter
     * @return bool
     */
    public function isParameter($parameter, &$normalized_parameter = null)
    {
        return $this->getParametersBag()->isParameter($parameter, $normalized_parameter);
    }

    /**
     * @return ParametersBag
     */
    protected function getParametersBag()
    {
        return $this->parameters;
    }

    /**
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function formatKey($key)
    {
        $key = $this->checkKey($key);
        return strtolower($key);
    }

    /**
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function checkKey($key)
    {
        if(!is_string($key) && !is_integer($key)) throw new \InvalidArgumentException('Argument $key must be string or integer instead of '.gettype($key));
        if(is_string($key) && ($key = trim($key)) === '') throw new \InvalidArgumentException('Argument $key cannot be empty');
        return $key;
    }
}
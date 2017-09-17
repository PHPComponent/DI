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

use PHPComponent\DI\Exceptions\InvalidServiceException;
use PHPComponent\DI\Reference\IServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class MethodCall implements IMethodCall
{

    /** @var string */
    private $method_name;

    /** @var array */
    private $arguments = array();

    /** @var null|IServiceReference|object|string */
    private $service;

    /**
     * MethodCall constructor.
     * @param string $method_name
     * @param array $arguments
     * @param null|IServiceReference|object|string $service
     */
    public function __construct($method_name, array $arguments = array(), $service = null)
    {
        $this->setMethodName($method_name);
        $this->arguments = $arguments;
        $this->setService($service);
    }
    
    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->method_name;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return null|object|IServiceReference|string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param null|IServiceReference|object|string $service
     */
    private function setService($service)
    {
        if($service === null || $service instanceof IServiceReference || is_object($service))
        {
            $this->service = $service;
        }
        elseif(is_string($service))
        {
            $service = trim($service);
            if($service === '') throw new InvalidServiceException('Argument $service cannot be empty string');
            $this->service = $service;
        }
        else
        {
            throw new InvalidServiceException('Invalid service '.gettype($service));
        }
    }

    /**
     * @param string $method_name
     */
    private function setMethodName($method_name)
    {
        if(!is_string($method_name) || ($method_name = trim($method_name)) === '') throw new \InvalidArgumentException('Argument $method_name be non-empty string instead of '.gettype($method_name));
        $this->method_name = $method_name;
    }
}
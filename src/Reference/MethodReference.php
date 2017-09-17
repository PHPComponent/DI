<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Reference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class MethodReference implements IMethodReference
{

    /** @var string */
    private $method_name;

    /** @var IServiceReference */
    private $service_reference;

    /** @var array */
    private $arguments = array();

    /**
     * MethodReference constructor.
     * @param $method_name
     * @param IServiceReference $service_reference
     * @param array $arguments
     * @throws \InvalidArgumentException
     */
    public function __construct($method_name, IServiceReference $service_reference, array $arguments = array())
    {
        $this->setMethodName($method_name);
        $this->service_reference = $service_reference;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->method_name;
    }

    /**
     * @return IServiceReference
     */
    public function getServiceReference()
    {
        return $this->service_reference;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $method_name
     * @throws \InvalidArgumentException
     */
    private function setMethodName($method_name)
    {
        if(!is_string($method_name))
            throw new \InvalidArgumentException('Argument $method_name must be string instead of '.gettype($method_name));
        $method_name = trim($method_name);
        if($method_name === '')
            throw new \InvalidArgumentException('Argument $method_name cannot be empty');
        $this->method_name = $method_name;
    }
}
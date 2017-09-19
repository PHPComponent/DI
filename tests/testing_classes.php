<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */

namespace PHPComponent\DI\Tests;

class ServiceWithTwoClasses
{
    private $service;

    private $service_2;

    public function __construct(Service $service, Service2 $service_2)
    {
        $this->service = $service;
        $this->service_2 = $service_2;
    }
}

class ServiceFactory
{
    public function createService()
    {
        return new Service();
    }
}

class ServiceWithClassAndValue
{
    private $service;

    private $value;

    public function __construct(Service $service, $value)
    {
        $this->service = $service;
        $this->value = $value;
    }
}

class ServiceWithMethodCall
{

    private $foo;
    public function setFoo()
    {
        $this->foo = 'foo';
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public static function callFoo()
    {
        return 'foo';
    }
}

class ServiceWithStaticFactoryMethod
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function createServiceWithFoo()
    {
        return new self('foo');
    }

    public static function createServiceWithBar()
    {
        return new self('bar');
    }

    public function getValue()
    {
        return $this->value;
    }
}

class ServiceWithDefaultValue
{
    public function __construct($value = ''){
        
    }
}

class ServiceWithMethodCallOtherService
{
    public function setOtherServiceFoo(ServiceWithMethodCall $other_service)
    {
        $other_service->setFoo();
    }
}

class ServiceWithGetter
{
    public function getFoo()
    {
        return 'foo';
    }
}

class ServiceWithValue
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class ServiceWithPublicProperty
{
    public $value;
}

class Service{}
class Service2{}
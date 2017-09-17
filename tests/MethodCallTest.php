<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Test;

use PHPComponent\DI\Reference\IServiceReference;
use PHPComponent\DI\MethodCall;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class MethodCallTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getValidValues
     * @param string $method_name
     * @param array $arguments
     * @param mixed $service
     */
    public function testMethodCall($method_name, $arguments, $service)
    {
        $method_call = new MethodCall($method_name, $arguments, $service);
        $this->assertSame($method_name, $method_call->getMethodName());
        $this->assertSame($arguments, $method_call->getArguments());
        $this->assertSame($service, $method_call->getService());
    }

    public function getValidValues()
    {
        return array(
            array('method', array(false), null),
            array('bar', array('string'), $this->getMockBuilder(IServiceReference::class)->getMock()),
            array('baz', array(), 'Foo'),
        );
    }

    /**
     * @dataProvider dataProviderInvalidMethodNames
     * @param mixed $method_name
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidMethodName($method_name)
    {
        new MethodCall($method_name);
    }

    public function dataProviderInvalidMethodNames()
    {
        return array(
            array(1),
            array(new \stdClass()),
            array(false),
            array(null),
            array(''),
            array(function(){}),
            array(array()),
        );
    }

    /**
     * @dataProvider getInvalidServiceValues
     * @param mixed $service
     * @expectedException \PHPComponent\DI\Exceptions\InvalidServiceException
     */
    public function testInvalidService($service)
    {
        new MethodCall('method', array(), $service);
    }

    public function getInvalidServiceValues()
    {
        return array(
            array(''),
            array(array()),
            array(false),
            array(true),
            array(1),
            array(1.2),
        );
    }
}

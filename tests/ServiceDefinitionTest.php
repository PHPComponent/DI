<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Tests;

use PHPComponent\DI\MethodCall;
use PHPComponent\DI\PropertySetter;
use PHPComponent\DI\Reference\MethodReference;
use PHPComponent\DI\Reference\ServiceReference;
use PHPComponent\DI\ServiceDefinition;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ServiceDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testServiceDefinition()
    {
        $service_definition = new ServiceDefinition('class');
        $service_definition->setShared(false);
        $service_definition->addArguments(array(1));
        $service_definition->setArguments(array(4, 5));
        $service_definition->addArguments(array(6));
        $this->assertSame(array(4, 5, 6), $service_definition->getArguments());
        $this->assertSame('class', $service_definition->getClassName());
        $this->assertFalse($service_definition->isShared());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClassName()
    {
        new ServiceDefinition('');
    }

    public function testMethodCalls()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|MethodCall $method_call */
        $method_call = $this->getMockBuilder(MethodCall::class)->setConstructorArgs(array('method', array(1)))->getMock();
        $service_definition = new ServiceDefinition('class');
        $this->assertSame($service_definition, $service_definition->addMethodCall($method_call));
        $this->assertTrue($service_definition->hasMethodsCalls());
        $this->assertSame(array($method_call), $service_definition->getMethodsCalls());
        $method_calls = array(
            $this->getMockBuilder(MethodCall::class)->setConstructorArgs(array('foo', array()))->getMock(),
            $this->getMockBuilder(MethodCall::class)->setConstructorArgs(array('bar', array()))->getMock(),
        );
        $service_definition->setMethodsCalls($method_calls);
        $this->assertSame($method_calls, $service_definition->getMethodsCalls());
    }

    public function testPropertiesSetters()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|PropertySetter $property_setter */
        $property_setter = $this->getMockBuilder(PropertySetter::class)->setConstructorArgs(array('property_name', 'value'))->getMock();
        $service_definition = new ServiceDefinition('class');
        $this->assertFalse($service_definition->hasPropertiesSetters());
        $this->assertSame($service_definition, $service_definition->addPropertySetter($property_setter));
        $this->assertTrue($service_definition->hasPropertiesSetters());
        $this->assertSame(array($property_setter), $service_definition->getPropertiesSetters());
        $properties_setters = array(
            $this->getMockBuilder(PropertySetter::class)->setConstructorArgs(array('property_name1', 1))->getMock(),
            $this->getMockBuilder(PropertySetter::class)->setConstructorArgs(array('property_name2', 2))->getMock(),
        );
        $service_definition->setPropertiesSetters($properties_setters);
        $this->assertSame($properties_setters, $service_definition->getPropertiesSetters());
    }

    public function testGetReflection()
    {
        $service_definition = new ServiceDefinition(Service::class);
        $this->assertInstanceOf(\ReflectionClass::class, $service_definition->getReflection());
    }

    /**
     * @dataProvider dataProviderFactoryMethodInputs
     * @param mixed $input
     * @param bool $valid
     */
    public function testSetFactoryMethod($input, $valid)
    {
        $service_definition = new ServiceDefinition('class');
        if($valid === false) $this->expectException('\InvalidArgumentException');

        $service_definition->setFactoryMethod($input);
        if($valid === true && $input !== null) $this->assertTrue($service_definition->hasFactoryMethod());
    }

    public function dataProviderFactoryMethodInputs()
    {
        $method_reference = $this->getMockBuilder(MethodReference::class)->setConstructorArgs(array(
            'method',
            $this->getMockBuilder(ServiceReference::class)->setConstructorArgs(array('key'))->getMock()
        ));
        return array(
            array(array('Class', 'method'), true),
            array(array($this, 'method'), true),
            array('method', true),
            array('Class::method', true),
            array($method_reference->getMock(), true),
            array(new \stdClass(), false),
            array(array('Class', 'method', 'second_method'), false),
            array(false, false),
            array(null, true),
            array(1, false),
            array(array(), false),
        );
    }
}

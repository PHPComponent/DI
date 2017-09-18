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

use PHPComponent\DI\Container;
use PHPComponent\DI\Helpers;
use PHPComponent\DI\ParametersBag;
use PHPComponent\DI\Reference\ServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class HelpersTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getValues
     * @param mixed $class
     * @param string $class_name
     * @param bool $valid
     */
    public function testIsClassTypeOf($class, $class_name, $valid)
    {
        $this->assertSame($valid, Helpers::isClassTypeOf($class, $class_name));
    }

    public function getValues()
    {
        return array(
            array(\LogicException::class, \Exception::class, true),
            array(\InvalidArgumentException::class, \LogicException::class, true),
            array(new \stdClass(), \Traversable::class, false),
            array(\stdClass::class, \Traversable::class, false),
            array(new \stdClass(), 'stdClass', true),
        );
    }

    public function testAutoWireSimpleArguments()
    {
        $container = $this->createContainerMock();
        $reflection_method = new \ReflectionMethod($this, 'methodWithTwoSimpleRequiredParameters');
        $all_arguments = array(1, false);
        $not_all_arguments = array(1);
        $this->assertEquals($all_arguments, Helpers::autoWireArguments($reflection_method, $all_arguments, $container));
        $this->assertEquals($not_all_arguments, Helpers::autoWireArguments($reflection_method, $not_all_arguments, $container));
    }

    public function methodWithTwoSimpleRequiredParameters($value_1, $value_2){}

    public function testAutoWireNotPassedAnyArguments()
    {
        $method_reflection = new \ReflectionMethod($this, 'methodWithTypeHintAndDefaultParameters');

        $service = new Service();
        $container = $this->createContainerMock();
        $container->expects($this->at(0))
            ->method('getServiceByClassName')
            ->with(Service::class)
            ->willReturn($service);

        $this->assertEquals(array($service, 'value_2'), Helpers::autoWireArguments($method_reflection, array(), $container));
    }

    public function testAutoWireNotPassedClass()
    {
        $method_reflection = new \ReflectionMethod($this, 'methodWithTypeHintAndDefaultParameters');
        $arguments = array('value_2');

        $service = new Service();
        $container = $this->createContainerMock();
        $container->expects($this->at(0))
            ->method('getServiceKeyByClassName')
            ->with(Service::class)
            ->willReturn('service');
        $container->expects($this->at(1))
            ->method('getServiceByClassName')
            ->with(Service::class)
            ->willReturn($service);

        $this->assertEquals(array(new ServiceReference('service'), 'value_2'), Helpers::autoWireArguments($method_reflection, $arguments, $container, Helpers::OBJECTS_AS_SERVICE_REFERENCE));
        $this->assertEquals(array($service, 'value_2'), Helpers::autoWireArguments($method_reflection, $arguments, $container, Helpers::OBJECTS_AS_OBJECTS));
    }

    public function testAutoWireDefaultValue()
    {
        $method_reflection = new \ReflectionMethod($this, 'methodWithTypeHintAndDefaultParameters');
        $service = new Service();
        $arguments = array($service);

        $container = $this->createContainerMock();
        $this->assertSame(array($service, 'value_2'), Helpers::autoWireArguments($method_reflection, $arguments, $container));
    }

    public function methodWithTypeHintAndDefaultParameters(Service $service, $argument_2 = 'value_2'){}

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Container
     */
    private function createContainerMock()
    {
        $parameters_bag = $this->getMockBuilder(ParametersBag::class)->setConstructorArgs(array())->getMock();
        $container = $this->getMockBuilder(Container::class)->setConstructorArgs(array($parameters_bag))->getMock();
        return $container;
    }
}
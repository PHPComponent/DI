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

use PHPComponent\DI\Container;
use PHPComponent\DI\ParametersBag;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /** @var Container */
    private $container;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_bag_mock */
        $parameters_bag_mock = $this->getMockBuilder(ParametersBag::class)
            ->setConstructorArgs(array())
            ->getMock();
        $this->container = new Container($parameters_bag_mock);
    }

    public function testService()
    {
        $service = new \stdClass();
        $this->container->addService('key', $service);
        $this->assertInstanceOf('stdClass', $this->container->getService('key'));
        $this->assertTrue($this->container->hasService('key'));
        $this->assertSame(array('key' => $service), $this->container->getServices());

        $service_2 = new \stdClass();
        $this->container->addService('key2', $service_2);
        $this->assertInstanceOf('stdClass', $this->container->getService('key2'));
        $this->assertTrue($this->container->hasService('key2'));
        $this->assertSame(array('key' => $service, 'key2' => $service_2), $this->container->getServices());
    }

    /**
     * @expectedException \PHPComponent\DI\Exceptions\ServiceAlreadyExistsException
     */
    public function testAddExistingService()
    {
        $this->container->addService('service', new \stdClass());
        $this->container->addService('service', new \stdClass());
    }

    /**
     * @dataProvider dataProviderWrongService
     * @expectedException \InvalidArgumentException
     */
    public function testAddWrongService($key, $value)
    {
        $this->container->addService($key, $value);
    }

    public function dataProviderWrongService()
    {
        return array(
            array('key', 1),
            array('key', 'string'),
            array('key', array()),
            array('key', 1.2),
            array('key', false),
            array('key', null),
            array('key', '1'),
            array('key', function(){}),
            array('', new \stdClass()),
        );
    }

    public function testGetNonExistentService()
    {
        $this->assertNull($this->container->getService('test'));
        $this->assertFalse($this->container->hasService('test'));
    }

    public function testParameters()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_bag_mock */
        $parameters_bag_mock = $this->getMockBuilder(ParametersBag::class)
            ->setConstructorArgs(array())
            ->setMethods(array('addParameter', 'getParameters', 'getParameter', 'addParameters', 'hasParameter', 'setParameters'))
            ->getMock();
        $parameters_bag_mock
            ->expects($this->at(0))
            ->method('getParameters')
            ->willReturn(array());
        $parameters_bag_mock
            ->expects($this->at(1))
            ->method('addParameter')
            ->with('parameter', 1);
        $parameters_bag_mock
            ->expects($this->at(2))
            ->method('getParameter')
            ->willReturn(1);

        $parameters = array(2 => 'value');
        $parameters_bag_mock
            ->expects($this->at(3))
            ->method('addParameters')
            ->with($parameters);
        $parameters_bag_mock
            ->expects($this->at(4))
            ->method('getParameters')
            ->willReturn(array('parameter' => 1, 2 => 'value'));
        $parameters_bag_mock
            ->expects($this->at(5))
            ->method('hasParameter')
            ->with(2)
            ->willReturn(true);
        $set_parameters = array(1 => 'value1', 2 => 'value2');
        $parameters_bag_mock
            ->expects($this->at(6))
            ->method('setParameters')
            ->with($set_parameters);
        $parameters_bag_mock
            ->expects($this->at(7))
            ->method('getParameters')
            ->willReturn($set_parameters);
        $container = new Container($parameters_bag_mock);

        $this->assertSame(array(), $container->getParameters());
        $container->addParameter('parameter', 1);
        $this->assertSame(1, $container->getParameter('parameter'));
        $container->addParameters($parameters);
        $this->assertSame(array('parameter' => 1, 2 => 'value'), $container->getParameters());
        $this->assertTrue($container->hasParameter(2));
        $container->setParameters($set_parameters);
        $this->assertSame($set_parameters, $container->getParameters());

    }

    public function testGetParametersBag()
    {
        $reflection = new \ReflectionClass($this->container);
        $method_reflection = $reflection->getMethod('getParametersBag');
        $method_reflection->setAccessible(true);
        $this->assertInstanceOf('PHPComponent\DI\ParametersBag', $method_reflection->invoke($this->container));
    }
}

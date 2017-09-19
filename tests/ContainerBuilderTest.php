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

use PHPComponent\DI\ArgumentCallback;
use PHPComponent\DI\ContainerBuilder;
use PHPComponent\DI\IServiceDefinition;
use PHPComponent\DI\MethodCall;
use PHPComponent\DI\Reference\MethodReference;
use PHPComponent\DI\ParametersBag;
use PHPComponent\DI\Reference\ServiceReference;
use PHPComponent\DI\ServiceDefinition;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{

    /** @var ContainerBuilder */
    private $container_builder;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->getMockBuilder(ParametersBag::class)
            ->setConstructorArgs(array())
            ->getMock();
        $this->container_builder = new ContainerBuilder($parameters_mock);
    }

    public function testGetServiceFactoryMethod()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->getMockBuilder(ParametersBag::class)
            ->setConstructorArgs(array())
            ->setMethods(null)
            ->getMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service_1', ServiceWithStaticFactoryMethod::class)
            ->setFactoryMethod('createServiceWithFoo');

        $container_builder->registerService('service_2', ServiceWithStaticFactoryMethod::class)
            ->setFactoryMethod(array(ServiceWithStaticFactoryMethod::class, 'createServiceWithBar'));

        $container_builder->registerService('service_factory', ServiceFactory::class);
        $container_builder->registerService('service', Service::class)
            ->setFactoryMethod(new MethodReference('createService', new ServiceReference('service_factory')));

        $service_1 = $container_builder->getService('service_1');
        $this->assertInstanceOf(ServiceWithStaticFactoryMethod::class, $service_1);
        $this->assertSame($service_1->getValue(), 'foo');

        $service_2 = $container_builder->getService('service_2');
        $this->assertInstanceOf(ServiceWithStaticFactoryMethod::class, $service_2);
        $this->assertSame($service_2->getValue(), 'bar');

        $service_3 = $container_builder->getService('service');
        $this->assertInstanceOf(Service::class, $service_3);
    }

    public function testGetServiceWithParameter()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->getMockBuilder(ParametersBag::class)
            ->setConstructorArgs(array(array('text' => 'Hello World!', 'color' => 'red')))
            ->setMethods(null)
            ->getMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service_with_value', ServiceWithValue::class)
            ->addArguments(array('%text%'));
        $container_builder->registerService('service', Service::class);
        $container_builder->registerService('service_with_class_and_value', ServiceWithClassAndValue::class)
            ->addArguments(array('%color%'));
        $container_builder->registerService('service_default_value', ServiceWithDefaultValue::class);
        $this->assertInstanceOf(ServiceWithValue::class, $container_builder->getService('service_with_value'));
        $this->assertInstanceOf(ServiceWithClassAndValue::class, $container_builder->getService('service_with_class_and_value'));
        $this->assertInstanceOf(ServiceWithDefaultValue::class, $container_builder->getService('service_default_value'));
    }

    public function testGetServiceWithTypeHint()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->createParametersMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service_with_two_classes', ServiceWithTwoClasses::class)
            ->addArguments(array(new ServiceReference('service')));
        $container_builder->registerService('service', Service::class);
        $container_builder->registerService('service_2', Service2::class);
        $this->assertInstanceOf(ServiceWithTwoClasses::class, $container_builder->getService('service_with_two_classes'));
    }

    public function testGetServiceWithMethodCall()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->createParametersMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service', ServiceWithMethodCall::class)
            ->addMethodCall(new MethodCall('setOtherServiceFoo', array(new ServiceReference('service')), new ServiceReference('calling_service')));
        $container_builder->registerService('calling_service', ServiceWithMethodCallOtherService::class);

        $service = $container_builder->getService('service');
        $this->assertInstanceOf(ServiceWithMethodCall::class, $service);
        $this->assertSame('foo', $service->getFoo());
    }

    public function testGetServiceWithMethodReference()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->createParametersMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service', ServiceWithStaticFactoryMethod::class)
            ->setArguments(array(new MethodReference('getFoo', new ServiceReference('service_with_getter'))));
        $container_builder->registerService('service_with_getter', ServiceWithGetter::class);

        $service = $container_builder->getService('service');
        $this->assertInstanceOf(ServiceWithStaticFactoryMethod::class, $service);
        $this->assertSame('foo', $service->getValue());
    }

    public function testGetServiceWithArgumentCallback()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->createParametersMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service', ServiceWithStaticFactoryMethod::class)
            ->setArguments(array(new ArgumentCallback(function(){ return true; })));

        $service = $container_builder->getService('service');
        $this->assertInstanceOf(ServiceWithStaticFactoryMethod::class, $service);
        $this->assertTrue($service->getValue());
    }

    public function testMethodCall()
    {
        $parameters_mock = $this->createParametersMock();

        $service_reference = $this->getMockBuilder(ServiceReference::class)
            ->setConstructorArgs(array('service'))
            ->getMock();
        $service_reference->expects($this->any())
            ->method('getServiceKey')
            ->willReturn('service');

        /** @var \PHPUnit_Framework_MockObject_MockObject|MethodCall $method_call */
        $method_call = $this->getMockBuilder(MethodCall::class)
            ->setConstructorArgs(array('setFoo', array(), $service_reference))
            ->getMock();
        $method_call->expects($this->at(0))
            ->method('getArguments')
            ->willReturn(array());
        $method_call->expects($this->at(1))
            ->method('getMethodName')
            ->willReturn('setFoo');
        $method_call->expects($this->any())
            ->method('getService')
            ->willReturn($service_reference);

        /** @var \PHPUnit_Framework_MockObject_MockObject|MethodCall $method_call_2 */
        $method_call_2 = $this->getMockBuilder(MethodCall::class)
            ->setConstructorArgs(array('callFoo', array(), ServiceWithMethodCall::class))
            ->getMock();
        $method_call_2->expects($this->at(0))
            ->method('getArguments')
            ->willReturn(array());
        $method_call_2->expects($this->at(1))
            ->method('getMethodName')
            ->willReturn('callFoo');
        $method_call_2->expects($this->any())
            ->method('getService')
            ->willReturn(ServiceWithMethodCall::class);


        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service', ServiceWithMethodCall::class);
        $container_builder->registerService('service_2', Service::class)
            ->addMethodCall($method_call)
            ->addMethodCall($method_call_2);

        $container_builder->getService('service_2');
        $this->assertSame('foo', $container_builder->getService('service')->getFoo());
    }

    public function testGetServiceByClassName()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ParametersBag $parameters_mock */
        $parameters_mock = $this->createParametersMock();

        $container_builder = new ContainerBuilder($parameters_mock);
        $container_builder->registerService('service', Service::class);
        $this->assertInstanceOf(Service::class, $container_builder->getServiceByClassName(Service::class));
        $this->assertSame(null, $container_builder->getServiceByClassName(Service2::class));
        $this->assertSame('service', $container_builder->getServiceKeyByClassName(Service::class));
        $this->assertSame(null, $container_builder->getServiceKeyByClassName(Service2::class));
    }

    public function testRegisterService()
    {
        $this->assertInstanceOf(IServiceDefinition::class, $this->container_builder->registerService('key', 'stdClass'));
        $this->assertInstanceOf(IServiceDefinition::class, $this->container_builder->getServiceDefinition('key'));
    }

    /**
     * @expectedException \PHPComponent\DI\Exceptions\NonExistentServiceException
     */
    public function testNonExistentServiceDefinition()
    {
        $this->container_builder->getService('key');
    }

    public function testGetServiceDefinition()
    {
        $this->container_builder->registerService('key', 'stdClass');
        $this->assertInstanceOf(IServiceDefinition::class, $this->container_builder->getServiceDefinition('key'));
        $this->assertNull($this->container_builder->getServiceDefinition('key_of_service'));
    }

    public function testHasServiceDefinition()
    {
        $this->container_builder->registerService('key', 'stdClass');
        $this->assertTrue($this->container_builder->hasServiceDefinition('key'));
    }

    public function testSetAndGetDefinitions()
    {
        $first_service = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs(array('stdClass'))
            ->getMock();
        $second_service = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs(array('stdClass'))
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceDefinition $third_service */
        $third_service = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs(array('stdClass'))
            ->getMock();
        $definitions = array($first_service, $second_service);
        $this->container_builder->addServiceDefinition('key', $third_service);
        $this->assertSame(array('key' => $third_service), $this->container_builder->getServicesDefinitions());
        $this->container_builder->setServicesDefinitions($definitions);
        $this->assertSame($definitions, $this->container_builder->getServicesDefinitions());
    }

    /**
     * @expectedException \PHPComponent\DI\Exceptions\ServiceDefinitionAlreadyExistsException
     */
    public function testAddServiceDefinition()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceDefinition $definition_mock */
        $definition_mock = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs(array('stdClass'))
            ->getMock();
        $this->assertInstanceOf(IServiceDefinition::class, $this->container_builder->addServiceDefinition('key', $definition_mock));
        $this->assertSame($definition_mock, $this->container_builder->getServiceDefinition('key'));
        $this->container_builder->addServiceDefinition('key', $definition_mock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParametersMock()
    {
        $parameters_mock = $this->getMockBuilder(ParametersBag::class)
            ->setMethods(null)
            ->getMock();
        return $parameters_mock;
    }
}


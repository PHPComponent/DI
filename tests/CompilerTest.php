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

use PHPComponent\DI\Compiler;
use PHPComponent\DI\ContainerBuilder;
use PHPComponent\DI\Extensions\MethodInjectExtension;
use PHPComponent\DI\IContainer;
use PHPComponent\DI\MethodCall;
use PHPComponent\DI\ParametersBag;
use PHPComponent\DI\PropertySetter;
use PHPComponent\DI\Reference\MethodReference;
use PHPComponent\DI\Reference\ServiceReference;
use PHPComponent\DI\ServiceDefinition;
use PHPComponent\DI\Tests\Service;
use PHPComponent\DI\Tests\ServiceFactory;
use PHPComponent\DI\Tests\ServiceWithGetter;
use PHPComponent\DI\Tests\ServiceWithMethodCall;
use PHPComponent\DI\Tests\ServiceWithMethodCallOtherService;
use PHPComponent\DI\Tests\ServiceWithPublicProperty;
use PHPComponent\DI\Tests\ServiceWithStaticFactoryMethod;
use PHPComponent\DI\Tests\ServiceWithValue;
use PHPComponent\PhpCodeGenerator\CodeFormatter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{

    public function testCompilerWithMethodReference()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $service_with_getter = $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs(array(ServiceWithGetter::class))
            ->getMock();
        $service_with_getter->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_with_getter->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithGetter::class);

        $service_arguments = array(ServiceWithPublicProperty::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithPublicProperty::class);
        $service_definition->expects($this->once())
            ->method('hasPropertiesSetters')
            ->willReturn(true);
        $service_definition->expects($this->once())
            ->method('getPropertiesSetters')
            ->willReturn(array(new PropertySetter('value', new MethodReference('getFoo', new ServiceReference('service_with_getter')))));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service_with_getter' => $service_with_getter, 'service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithPublicProperty::class, $service);
        $this->assertSame('foo', $service->value);
    }

    public function testCompilerWithContainerReference()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $container_builder = $this->createContainerBuilder($parameters);

        $service_arguments = array(ServiceWithPublicProperty::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithPublicProperty::class);
        $service_definition->expects($this->once())
            ->method('hasPropertiesSetters')
            ->willReturn(true);
        $service_definition->expects($this->once())
            ->method('getPropertiesSetters')
            ->willReturn(array(new PropertySetter('value', $container_builder)));

        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithPublicProperty::class, $service);
        $this->assertInstanceOf(IContainer::class, $service->value);
        $this->assertSame($container, $service->value);
    }

    public function testCompilerWithPropertySetter()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();
        $code_formatter->expects($this->once())
            ->method('printValue')
            ->with('test')
            ->willReturn('\'test\'');

        $service_arguments = array(ServiceWithPublicProperty::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithPublicProperty::class);
        $service_definition->expects($this->once())
            ->method('hasPropertiesSetters')
            ->willReturn(true);
        $service_definition->expects($this->once())
            ->method('getPropertiesSetters')
            ->willReturn(array(new PropertySetter('value', 'test')));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithPublicProperty::class, $service);
        $this->assertSame('test', $service->value);
    }

    public function testCompilerWithParameters()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $service_arguments = array(ServiceWithValue::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array('%value%'));
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithValue::class);

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array('value' => 'test'));
        $container_builder->expects($this->once())
            ->method('isParameter')
            ->with('%value%', null)
            ->willReturnCallback(function($parameter, &$attribute_name)
            {
                $attribute_name = 'value';
                return true;
            });
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithValue::class, $service);
        $this->assertSame('test', $service->getValue());
    }

    public function testCompilerWithStaticFactoryMethod()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $service_arguments = array(ServiceWithStaticFactoryMethod::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithStaticFactoryMethod::class);
        $service_definition->expects($this->at(2))
            ->method('hasFactoryMethod')
            ->willReturn(true);
        $service_definition->expects($this->at(4))
            ->method('getFactoryMethod')
            ->willReturn(array(ServiceWithStaticFactoryMethod::class, 'createServiceWithBar'));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithStaticFactoryMethod::class, $service);
        $this->assertSame('bar', $service->getValue());

    }

    public function testCompilerWithServiceReferenceMethodCall()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $other_service_arguments = array(ServiceWithMethodCallOtherService::class);
        $other_service = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($other_service_arguments)
            ->getMock();
        $other_service->expects($this->any())
            ->method('isShared')
            ->willReturn(true);
        $other_service->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $other_service->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithMethodCallOtherService::class);

        $service_arguments = array(ServiceWithMethodCall::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->any())
            ->method('isShared')
            ->willReturn(true);
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithMethodCall::class);
        $service_definition->expects($this->at(8))
            ->method('hasMethodsCalls')
            ->willReturn(true);
        $service_definition->expects($this->at(9))
            ->method('getMethodsCalls')
            ->willReturn(array(new MethodCall('setOtherServiceFoo', array(new ServiceReference('service')), new ServiceReference('other_service'))));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->atLeast(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('other_service' => $other_service, 'service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithMethodCall::class, $service);
        $this->assertSame('foo', $service->getFoo());
    }

    public function testCompilerWithSelfMethodCall()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $service_arguments = array(ServiceWithMethodCall::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceWithMethodCall::class);
        $service_definition->expects($this->at(8))
            ->method('hasMethodsCalls')
            ->willReturn(true);
        $service_definition->expects($this->at(9))
            ->method('getMethodsCalls')
            ->willReturn(array(new MethodCall('setFoo')));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->at(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service = $container->getService('service');
        $this->assertInstanceOf(ServiceWithMethodCall::class, $service);
        $this->assertSame('foo', $service->getFoo());
    }

    public function testCompilerMethodReferenceFactoryMethod()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $factory_arguments = array(ServiceFactory::class);
        $factory_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($factory_arguments)
            ->getMock();
        $factory_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $factory_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(ServiceFactory::class);

        $service_arguments = array(Service::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_arguments)
            ->getMock();
        $service_definition->expects($this->atLeast(1))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->atLeast(2))
            ->method('getClassName')
            ->willReturn(Service::class);
        $service_definition->expects($this->at(2))
            ->method('hasFactoryMethod')
            ->willReturn(true);
        $service_definition->expects($this->at(4))
            ->method('getFactoryMethod')
            ->willReturn(new MethodReference('createService', new ServiceReference('service_factory')));

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->at(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service_factory' => $factory_definition, 'service' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $this->assertInstanceOf(Service::class, $container->getService('service'));
    }

    public function testCompilerSharedService()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $service_definition_arguments = array(ServiceFactory::class);
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setConstructorArgs($service_definition_arguments)
            ->getMock();
        $service_definition->expects($this->any())
            ->method('getClassName')
            ->willReturn(ServiceFactory::class);
        $service_definition->expects($this->at(4))
            ->method('getArguments')
            ->willReturn(array());
        $service_definition->expects($this->any())
            ->method('isShared')
            ->willReturn(true);

        $container_builder = $this->createContainerBuilder($parameters);
        $container_builder->expects($this->atLeast(1))
            ->method('getParameters')
            ->willReturn(array());
        $container_builder->expects($this->at(1))
            ->method('getServicesDefinitions')
            ->willReturn(array('service_factory' => $service_definition));

        $compiler = new Compiler($container_builder, $code_formatter);
        $container = createContainer($compiler);
        $service_factory = $container->getService('service_factory');
        $this->assertInstanceOf(ServiceFactory::class, $service_factory);
        $this->assertSame($service_factory, $container->getService('service_factory'));
    }

    public function testExtensions()
    {
        /** @var CodeFormatter|\PHPUnit_Framework_MockObject_MockObject $code_formatter */
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        $compiler = new Compiler($this->createContainerBuilder(), $code_formatter);

        $extension = new MethodInjectExtension();

        $this->assertFalse($compiler->hasExtension(MethodInjectExtension::getExtensionName()));
        $this->assertSame($compiler, $compiler->addExtension($extension));
        $this->assertTrue($compiler->hasExtension(MethodInjectExtension::getExtensionName()));
        $this->assertSame(array(strtolower(MethodInjectExtension::getExtensionName()) => $extension), $compiler->getExtensions());
        $this->assertSame($extension, $compiler->getExtension(MethodInjectExtension::getExtensionName()));
        $this->assertNull($compiler->getExtension('NonExistingExtension'));
        try
        {
            $compiler->addExtension($extension);
            $this->fail('Expected exception');
        }
        catch(\InvalidArgumentException $e)
        {
        }
    }

    public function createContainerBuilder(&$parameters_bag = null)
    {
        /** @var ParametersBag|\PHPUnit_Framework_MockObject_MockObject $parameters_bag */
        $parameters_bag = $this->getMockBuilder(ParametersBag::class)
            ->getMock();

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container_builder */
        $container_builder = $this->getMockBuilder(ContainerBuilder::class)
            ->setConstructorArgs(array($parameters_bag))
            ->getMock();

        return $container_builder;
    }
}

<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Tests\Extensions;

use PHPComponent\DI\Compiler;
use PHPComponent\DI\ContainerBuilder;
use PHPComponent\DI\Extensions\MethodInjectExtension;
use PHPComponent\DI\ParametersBag;
use PHPComponent\DI\ServiceDefinition;
use PHPComponent\DI\Test\Service;
use PHPComponent\PhpCodeGenerator\CodeFormatter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class MethodInjectExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetName()
    {
        $this->assertSame('MethodInjectExtension', MethodInjectExtension::getExtensionName());
    }

    public function testInject()
    {
        /** @var ParametersBag|\PHPUnit_Framework_MockObject_MockObject $parameters_bag */
        $parameters_bag = $this->getMockBuilder(ParametersBag::class)
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceDefinition $service_definition */
        $service_definition = $this->getMockBuilder(ServiceDefinition::class)
            ->setMethods(array('getReflection'))
            ->setConstructorArgs(array(ServiceWithMethodInjection::class))
            ->getMock();
        $service_definition->expects($this->at(0))
            ->method('getReflection')
            ->willReturn(new \ReflectionClass(ServiceWithMethodInjection::class));

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container_builder */
        $container_builder = $this->getMockBuilder(ContainerBuilder::class)
            ->setConstructorArgs(array($parameters_bag))
            ->getMock();
        $container_builder->expects($this->at(0))
            ->method('getServicesDefinitions')
            ->willReturn(array($service_definition));
        $container_builder->expects($this->at(1))
            ->method('getServiceKeyByClassName')
            ->willReturn('service');

        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();

        /** @var Compiler|\PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder(Compiler::class)
            ->setConstructorArgs(array($container_builder, $code_formatter))
            ->getMock();
        $compiler->expects($this->any())
            ->method('getContainerBuilder')
            ->willReturn($container_builder);

        $method_inject_extension = new MethodInjectExtension();
        $method_inject_extension->setCompiler($compiler);
        $this->assertFalse($service_definition->hasMethodsCalls());
        $method_inject_extension->beforeCompile();
        $this->assertTrue($service_definition->hasMethodsCalls());
    }
}

class ServiceWithMethodInjection
{

    /** @var Service */
    private $service;

    /**
     * @di-inject
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }
}
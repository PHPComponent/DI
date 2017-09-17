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
use PHPComponent\DI\Extensions\Extension;
use PHPComponent\DI\ParametersBag;
use PHPComponent\PhpCodeGenerator\CodeFormatter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testExtension()
    {
        /** @var ParametersBag|\PHPUnit_Framework_MockObject_MockObject $parameters_bag */
        $parameters_bag = $this->getMockBuilder(ParametersBag::class)
            ->getMock();
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container_builder */
        $container_builder = $this->getMockBuilder(ContainerBuilder::class)
            ->setConstructorArgs(array($parameters_bag))
            ->getMock();
        $code_formatter = $this->getMockBuilder(CodeFormatter::class)
            ->getMock();
        /** @var Compiler|\PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder(Compiler::class)
            ->setConstructorArgs(array($container_builder, $code_formatter))
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Extension $extension */
        $extension = $this->getMockForAbstractClass(Extension::class);
        $this->assertNull($extension->getCompiler());
        $extension->setCompiler($compiler);
        $this->assertSame($compiler, $extension->getCompiler());
    }

    public function testExtensionName()
    {
        $this->assertSame('Extension', Extension::getExtensionName());
    }
}

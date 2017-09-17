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
use PHPComponent\DI\ContainerLoader;
use PHPComponent\DI\ParametersBag;
use PHPComponent\PhpCodeGenerator\CodeFormatter;
use PHPComponent\PhpCodeGenerator\PhpCodeFragment;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ContainerLoaderTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        @mkdir(dirname(__FILE__).'/tmp', 0777, true);
    }

    protected function tearDown()
    {
        /** @var \FilesystemIterator|\SplFileInfo[] $files */
        $files = new \FilesystemIterator(dirname(__FILE__).'/tmp', \FilesystemIterator::CURRENT_AS_FILEINFO);
        foreach($files as $file)
        {
            @unlink($file->getPathname());
        }
        @rmdir(dirname(__FILE__).'/tmp');
    }

    public function testLoad()
    {
        $compiler = $this->createCompilerMock();

        $container_loader = new ContainerLoader($compiler, __FILE__, dirname(__FILE__).'/tmp');
        $class_name = $container_loader->load();
        $this->assertFileExists(dirname(__FILE__).'/tmp/'.$class_name.'.php');
    }

    /**
     * @return Compiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCompilerMock()
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
        /** @var PhpCodeFragment|\PHPUnit_Framework_MockObject_MockObject $php_code */
        $php_code = $this->getMockBuilder(PhpCodeFragment::class)
            ->getMock();
        /** @var Compiler|\PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder(Compiler::class)
            ->setConstructorArgs(array($container_builder, $code_formatter))
            ->getMock();
        $compiler->expects($this->at(0))
            ->method('compile')
            ->willReturn($php_code);
        $compiler->expects($this->at(1))
            ->method('getCodeFormatter')
            ->willReturn($code_formatter);

        return $compiler;
    }
}

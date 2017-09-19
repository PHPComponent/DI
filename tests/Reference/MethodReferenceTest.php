<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Tests\Reference;

use PHPComponent\DI\Reference\MethodReference;
use PHPComponent\DI\Reference\ServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class MethodReferenceTest extends \PHPUnit_Framework_TestCase
{

    public function testMethodReference()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceReference $service_reference */
        $service_reference = $this->getMockBuilder(ServiceReference::class)->setConstructorArgs(array('key'))->getMock();
        $method_reference = new MethodReference('method', $service_reference);
        $this->assertSame('method', $method_reference->getMethodName());
        $this->assertSame(array(), $method_reference->getArguments());
        $this->assertSame($service_reference, $method_reference->getServiceReference());
    }

    /**
     * @dataProvider dataProviderInvalidMethodNames
     * @param mixed $method_name
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidMethodName($method_name)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ServiceReference $service_reference */
        $service_reference = $this->getMockBuilder(ServiceReference::class)->setConstructorArgs(array('key'))->getMock();
        new MethodReference($method_name, $service_reference);
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
}

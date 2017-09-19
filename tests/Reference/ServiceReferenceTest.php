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

use PHPComponent\DI\Reference\ServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ServiceReferenceTest extends \PHPUnit_Framework_TestCase
{

    public function testServiceReference()
    {
        $service_key = 'service';
        $service_reference = new ServiceReference($service_key);
        $this->assertSame($service_key, $service_reference->getServiceKey());
        $this->assertSame($service_key, (string) $service_reference);
        $this->assertInstanceOf(ServiceReference::class, ServiceReference::createServiceReference($service_key));
    }

    /**
     * @dataProvider dataProviderWrongServiceKeys
     * @param mixed $service_key
     * @expectedException \InvalidArgumentException
     */
    public function testWrongServiceReferenceKey($service_key)
    {
        new ServiceReference($service_key);
    }

    public function dataProviderWrongServiceKeys()
    {
        return array(
            array(1),
            array(array()),
            array(false),
            array(null),
            array(new \stdClass()),
            array(''),
            array(function(){}),
        );
    }
}

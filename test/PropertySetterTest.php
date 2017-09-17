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

use PHPComponent\DI\PropertySetter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class PropertySetterTest extends \PHPUnit_Framework_TestCase
{

    public function testPropertySetter()
    {
        $property_setter = new PropertySetter('property_name', 'value');
        $this->assertSame('property_name', $property_setter->getPropertyName());
        $this->assertSame('value', $property_setter->getValue());
    }

    /**
     * @dataProvider getInvalidValues
     * @expectedException \InvalidArgumentException
     * @param $property_name
     * @param $value
     */
    public function testInvalidValues($property_name, $value)
    {
        new PropertySetter($property_name, $value);
    }

    public function getInvalidValues()
    {
        return array(
            array('', ''),
            array(false, ''),
            array(true, ''),
            array(1, ''),
            array(1.2, ''),
            array(0, ''),
            array(new \stdClass(), ''),
            array(null, ''),
            array(array(), ''),
        );
    }
}

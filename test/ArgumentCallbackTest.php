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

use PHPComponent\DI\ArgumentCallback;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ArgumentCallbackTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getValidValues
     * @param mixed $value
     */
    public function testValidValues($value)
    {
        $argument_callback = new ArgumentCallback($value);
        $this->assertSame($value, $argument_callback->getCallback());
    }

    public function getValidValues()
    {
        return array(
            array(array(ArgumentCallbackTest::class, 'getFoo')),
            array(array($this, 'getBar')),
            array('PHPComponent\DI\Test\ArgumentCallbackTest::getFoo'),
            array(function(){return null;}),
        );
    }

    public function getBar()
    {
        return 'bar';
    }

    public static function getFoo()
    {
        return 'foo';
    }

    /**
     * @dataProvider getInvalidValues
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testInvalidValues($value)
    {
        new ArgumentCallback($value);
    }

    public function getInvalidValues()
    {
        return array(
            array(null),
            array(false),
            array(true),
            array(new \stdClass()),
            array(array()),
            array(array('Foo', 'bar')),
            array('Foo::bar'),
            array('test'),
            array(1),
            array(1.2),
        );
    }
}

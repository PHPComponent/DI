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

use PHPComponent\DI\ParametersBag;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ParametersBagTest extends \PHPUnit_Framework_TestCase
{

    /** @var ParametersBag */
    private $parameters_bag;

    protected function setUp()
    {
        $this->parameters_bag = new ParametersBag(array());
    }

    public function testAddParameter()
    {
        $parameter = new \stdClass();
        $this->parameters_bag->addParameter('key', $parameter);
        $this->assertSame($parameter, $this->parameters_bag->getParameter('key'));
    }

    /**
     * @expectedException \PHPComponent\DI\Exceptions\ParameterAlreadyExistsException
     */
    public function testAddExistingParameter()
    {
        $parameter = new \stdClass();
        $this->parameters_bag->addParameter('key', $parameter);
        $this->parameters_bag->addParameter('key', $parameter);
    }

    public function testGetParameter()
    {
        $this->parameters_bag->addParameter('key', 'value');
        $this->assertSame('value', $this->parameters_bag->getParameter('key'));
        $this->assertNull($this->parameters_bag->getParameter('test'));
    }

    public function testAddParameters()
    {
        $this->parameters_bag->addParameter('key', 'value');
        $data = array(
            1 => 'value',
            2 => 'value2'
        );
        $this->parameters_bag->addParameters($data);
        $this->assertSame(array(
            'key' => 'value',
            1 => 'value',
            2 => 'value2'
        ), $this->parameters_bag->getParameters());
    }

    public function testSetParameters()
    {
        $this->parameters_bag->addParameter('key', 'value');
        $this->assertSame(array('key' => 'value'), $this->parameters_bag->getParameters());
        $this->parameters_bag->setParameters(array(1 , 2));
        $this->assertSame(array(1, 2), $this->parameters_bag->getParameters());
        $this->parameters_bag->setParameter('key', false);
        $this->assertFalse($this->parameters_bag->getParameter('key'));
        $this->assertFalse($this->parameters_bag['key']);
        $this->parameters_bag['second_key'] = 'Testing value';
        $this->assertTrue(isset($this->parameters_bag['second_key']));
        unset($this->parameters_bag['second_key']);
        $this->assertNull($this->parameters_bag['second_key']);
    }

    public function testResolveParameter()
    {
        $this->parameters_bag->addParameter('class.argument', 'value');
        $this->assertSame('value', $this->parameters_bag->resolveParameter('%class.argument%'));
        $this->assertSame('%class.secondargument%', $this->parameters_bag->resolveParameter('%class.secondargument%'));
        $this->assertTrue($this->parameters_bag->isParameter('%class.argument%', $normalized_parameter));
        $this->assertSame('class.argument', $normalized_parameter);
    }

    public function testFormatKey()
    {
        $reflection = new \ReflectionClass($this->parameters_bag);
        $method_reflection = $reflection->getMethod('formatKey');
        $method_reflection->setAccessible(true);
        $formatted_result = $method_reflection->invoke($this->parameters_bag, 'Test');
        $this->assertSame(0, strcmp($formatted_result, 'test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCheckKey()
    {
        $reflection = new \ReflectionClass($this->parameters_bag);
        $method_reflection = $reflection->getMethod('checkKey');
        $method_reflection->setAccessible(true);
        try
        {
            $method_reflection->invoke($this->parameters_bag, 1);
        }
        catch(\InvalidArgumentException $e)
        {
            $this->fail('key 1 is not wrong');
        }
        $method_reflection->invoke($this->parameters_bag, array());
    }

    public function testCount()
    {
        $this->parameters_bag->setParameters(array(1, 2, 3));
        $this->assertSame(3, count($this->parameters_bag));
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf('\Iterator', $this->parameters_bag->getIterator());
    }
}

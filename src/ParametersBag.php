<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI;

use PHPComponent\DI\Exceptions\ParameterAlreadyExistsException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ParametersBag implements \Countable, \IteratorAggregate, \ArrayAccess
{

    /** @var array */
    private $parameters = array();

    /**
     * ParametersBag constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->setParameters($data);
    }

    /**
     * @param string $key
     * @param mixed $parameter
     * @throws ParameterAlreadyExistsException
     */
    public function addParameter($key, $parameter)
    {
        $key = $this->formatKey($key);
        if($this->hasParameter($key))
            throw new ParameterAlreadyExistsException('Parameter '.$key.' already exists');
        $this->parameters[$key] = $parameter;
    }

    /**
     * @param array $parameters
     * @throws ParameterAlreadyExistsException
     */
    public function addParameters(array $parameters)
    {
        foreach($parameters as $key => $parameter)
        {
            $this->addParameter($key, $parameter);
        }
    }

    /**
     * @param string $key
     * @param mixed $parameter
     */
    public function setParameter($key, $parameter)
    {
        $key = $this->formatKey($key);
        $this->parameters[$key] = $parameter;
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array();
        $this->addParameters($parameters);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getParameter($key)
    {
        $key = $this->formatKey($key);
        if($this->hasParameter($key)) return $this->parameters[$key];
        return null;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter($key)
    {
        $key = $this->formatKey($key);
        return array_key_exists($key, $this->parameters);
    }

    /**
     * @param string $parameter
     * @return mixed|null
     */
    public function resolveParameter($parameter)
    {
        if($this->containsParameter($parameter, $normalized_parameter, $before_parameter, $resolved_parameter, $after_parameter))
        {
            return $before_parameter.$resolved_parameter.$after_parameter;
        }

        return $parameter;
    }

    /**
     * @param string $parameter
     * @param null|string $normalized_parameter
     * @param null|string $before_parameter
     * @param null|string $resolved_parameter
     * @param null|string $after_parameter
     * @return bool
     */
    public function containsParameter($parameter, &$normalized_parameter = null, &$before_parameter = null, &$resolved_parameter = null, &$after_parameter = null)
    {
        $normalized_parameter = null;
        $before_parameter = null;
        $resolved_parameter = null;
        $after_parameter = null;

        if(is_string($parameter))
        {
            if(preg_match('#(?P<before_parameter>.*)\%(?P<parameter>[^%\s]+)\%(?P<after_parameter>.*)#', $parameter, $matches))
            {
                if(!$this->hasParameter($matches['parameter'])) return false;
                $normalized_parameter = $this->formatKey($matches['parameter']);
                $resolved_parameter = $this->getParameter($matches['parameter']);
                $before_parameter = $matches['before_parameter'];
                $after_parameter = $matches['after_parameter'];
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $parameter
     * @param null|string $normalized_parameter
     * @return bool
     */
    public function isParameter($parameter, &$normalized_parameter = null)
    {
        if(is_string($parameter) && preg_match('#^\%([^%\s]+)\%$#', $parameter))
        {
            $normalized_parameter = $this->formatKey(trim($parameter, "%"));
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function formatKey($key)
    {
        $this->checkKey($key);
        return strtolower($key);
    }

    /**
     * @param string $key
     * @throws \InvalidArgumentException
     */
    protected function checkKey($key)
    {
        if(!is_string($key) && !is_int($key))
            throw new \InvalidArgumentException('Argument $key must be string or int instead of '.gettype($key));
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->hasParameter($offset);
    }

    /**
     * @param string $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->getParameter($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setParameter($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->parameters[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->parameters);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }
}
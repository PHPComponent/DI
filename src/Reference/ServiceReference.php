<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Reference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ServiceReference implements IServiceReference
{

    /** @var string */
    private $key;

    /**
     * ServiceReference constructor.
     * @param string $key
     */
    public function __construct($key)
    {
        $this->setKey($key);
    }

    /**
     * @return string
     */
    public function getServiceKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getServiceKey();
    }

    /**
     * @param string $key
     * @return ServiceReference
     */
    public static function createServiceReference($key)
    {
        return new self($key);
    }

    /**
     * @param string $key
     * @throws \InvalidArgumentException
     */
    private function setKey($key)
    {
        if(!is_string($key) || ($key = trim($key)) === '')
            throw new \InvalidArgumentException('Argument $key must be non-empty string instead of '.gettype($key));
        $this->key = $key;
    }
}
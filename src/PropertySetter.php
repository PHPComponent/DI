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

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class PropertySetter implements IPropertySetter
{

    /** @var string */
    private $property_name;

    /** @var mixed */
    private $property_value;

    /**
     * PropertySetter constructor.
     * @param string $property_name
     * @param mixed $property_value
     */
    public function __construct($property_name, $property_value)
    {
        $this->setPropertyName($property_name);
        $this->property_value = $property_value;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->property_value;
    }

    /**
     * @param string $property_name
     */
    private function setPropertyName($property_name)
    {
        if(!is_string($property_name))
        {
            throw new \InvalidArgumentException('Argument $property_name must be string instead of '.gettype($property_name));
        }
        if(($property_name = trim($property_name)) === '')
        {
            throw new \InvalidArgumentException('Argument $property_name must not be empty');
        }
        $this->property_name = $property_name;
    }
}
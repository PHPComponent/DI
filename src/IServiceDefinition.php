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

use PHPComponent\DI\Reference\IMethodReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
interface IServiceDefinition
{

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments);

    /**
     * @param array $arguments
     * @return $this
     */
    public function addArguments(array $arguments);

    /**
     * @return array
     */
    public function getArguments();

    /**
     * @param IPropertySetter[] $properties_setters
     * @return $this
     */
    public function setPropertiesSetters(array $properties_setters);

    /**
     * @param IPropertySetter $property_setter
     * @return $this
     */
    public function addPropertySetter(IPropertySetter $property_setter);

    /**
     * @return IPropertySetter[]
     */
    public function getPropertiesSetters();

    /**
     * @return bool
     */
    public function hasPropertiesSetters();

    /**
     * @param IMethodCall[] $methods_calls
     * @return $this
     */
    public function setMethodsCalls(array $methods_calls);

    /**
     * @param IMethodCall $method_call
     * @return $this
     */
    public function addMethodCall(IMethodCall $method_call);

    /**
     * @return IMethodCall[]
     */
    public function getMethodsCalls();

    /**
     * @return bool
     */
    public function hasMethodsCalls();

    /**
     * @param null|string|array|IMethodReference $factory_method
     * @return $this
     */
    public function setFactoryMethod($factory_method);

    /**
     * @return null|string|array|IMethodReference
     */
    public function getFactoryMethod();

    /**
     * @return bool
     */
    public function hasFactoryMethod();

    /**
     * @param bool $shared
     * @return $this
     */
    public function setShared($shared);

    /**
     * @return bool
     */
    public function isShared();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return \ReflectionClass
     */
    public function getReflection();
}
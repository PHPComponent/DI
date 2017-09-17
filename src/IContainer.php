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
interface IContainer
{

    /**
     * @param string $key
     * @param object $service
     * @return $this
     */
    public function addService($key, $service);

    /**
     * @param string $key
     * @return object|null
     */
    public function getService($key);

    /**
     * @return object[]
     */
    public function getServices();

    /**
     * @param string $key
     * @return bool
     */
    public function hasService($key);

    /**
     * @param string $class_name
     * @return null|object
     */
    public function getServiceByClassName($class_name);

    /**
     * @param string $class_name
     * @return string|null
     */
    public function getServiceKeyByClassName($class_name);

    /**
     * @param string $key
     * @param mixed $parameter
     * @return $this
     */
    public function addParameter($key, $parameter);

    /**
     * @param array $parameters
     * @return $this
     */
    public function addParameters(array $parameters);

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param string $key
     * @return mixed
     */
    public function getParameter($key);

    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter($key);

    /**
     * @param string $parameter
     * @return mixed
     */
    public function resolveParameter($parameter);

    /**
     * @param string $parameter
     * @param null|string $normalized_parameter
     * @return bool
     */
    public function isParameter($parameter, &$normalized_parameter = null);
}
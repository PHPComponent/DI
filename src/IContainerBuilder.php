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
interface IContainerBuilder extends IContainer
{

    /**
     * @param string $key
     * @param string $service_name
     * @return IServiceDefinition
     */
    public function registerService($key, $service_name);

    /**
     * @param string $key
     * @param IServiceDefinition $definition
     * @return IServiceDefinition
     */
    public function addServiceDefinition($key, IServiceDefinition $definition);

    /**
     * @param IServiceDefinition[] $services_definitions
     */
    public function setServicesDefinitions(array $services_definitions);

    /**
     * @param string $key
     * @return null|IServiceDefinition
     */
    public function getServiceDefinition($key);

    /**
     * @return IServiceDefinition[]
     */
    public function getServicesDefinitions();

    /**
     * @param string $key
     * @return bool
     */
    public function hasServiceDefinition($key);
}
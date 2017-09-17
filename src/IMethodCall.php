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

use PHPComponent\DI\Reference\IServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
interface IMethodCall
{
         
    /**
     * @return string
     */
    public function getMethodName();

    /**
     * @return array
     */
    public function getArguments();

    /**
     * @return null|IServiceReference|object|string
     */
    public function getService();
}
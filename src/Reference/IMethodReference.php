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
interface IMethodReference
{
    
    /**
     * @return string
     */
    public function getMethodName();

    /**
     * @return IServiceReference
     */
    public function getServiceReference();

    /**
     * @return array
     */
    public function getArguments();
}
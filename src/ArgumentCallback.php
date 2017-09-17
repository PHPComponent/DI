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
class ArgumentCallback implements IArgumentCallback
{

    /** @var callable */
    private $callback;

    /**
     * ArgumentCallback constructor.
     * @param callable $callback
     */
    public function __construct($callback)
    {
        if(!is_callable($callback)) throw new \InvalidArgumentException('Argument $callback must be valid callback instead of '.gettype($callback));
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
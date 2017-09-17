<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\DI\Extensions;

use PHPComponent\DI\Compiler;
use PHPComponent\DI\IContainerBuilder;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
abstract class Extension
{

    /** @var Compiler */
    private $compiler;

    /**
     * @param Compiler $compiler
     * @return $this
     */
    public function setCompiler(Compiler $compiler)
    {
        if($this->compiler instanceof Compiler) throw new \LogicException('Compiler is already set');
        $this->compiler = $compiler;
        return $this;
    }

    /**
     * @return Compiler
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * @return void
     */
    public function beforeCompile()
    {

    }

    /**
     * @return string
     */
    public static function getExtensionName()
    {
        $reflection_object = new \ReflectionClass(get_called_class());
        return $reflection_object->getShortName();
    }

    /**
     * @return IContainerBuilder
     */
    protected function getContainer()
    {
        return $this->getCompiler()->getContainerBuilder();
    }
}
<?php
/*
 * This file is part of PHPComponent/DI.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */

$dir_name = dirname(__FILE__);
require_once $dir_name.'/../vendor/autoload.php';
require_once $dir_name.'/testing_classes.php';
define('TEMP_DIR', $dir_name.'/tmp');
@mkdir(TEMP_DIR);

/**
 * @param \PHPComponent\DI\Compiler $compiler
 * @return \PHPComponent\DI\Container
 */
function createContainer($compiler)
{
    $class_name = 'Container_'.md5(rand());
    $php_code_fragment = $compiler->compile($class_name);
    file_put_contents(TEMP_DIR.'/code.php', $php_code_fragment->getCode(\PHPComponent\PhpCodeGenerator\DefaultCodeFormatter::getInstance()));
    require TEMP_DIR.'/code.php';
    return new $class_name();
}
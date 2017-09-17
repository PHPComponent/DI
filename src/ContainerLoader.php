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
class ContainerLoader
{

    /** @var Compiler */
    private $compiler;

    /** @var string */
    private $config_file_path;

    /** @var string */
    private $temp_directory;

    /**
     * ContainerLoader constructor.
     * @param Compiler $compiler
     * @param string $config_file_path
     * @param string $temp_directory
     */
    public function __construct(Compiler $compiler, $config_file_path, $temp_directory)
    {
        $this->compiler = $compiler;
        $this->config_file_path = $config_file_path;
        $this->temp_directory = $temp_directory;
    }

    /**
     * @param null|string $key
     * @return string
     */
    public function load($key = null)
    {
        $class_name = 'Container_'.substr(md5(serialize($key)), 0, 5);
        if(!class_exists($class_name, false))
        {
            $this->generateContainer($class_name);
            require_once $this->temp_directory.'/'.$class_name.'.php';
        }
        return $class_name;
    }

    /**
     * @param string $class_name
     */
    private function generateContainer($class_name)
    {
        $file_mtime = filemtime($this->config_file_path);
        $last_modified_hash = md5($file_mtime);

        $meta_file = $this->temp_directory.'/'.$class_name.'.php.meta';
        if(!file_exists($meta_file))
        {
            file_put_contents($meta_file, '');
        }
        $meta_file_content = file_get_contents($meta_file);
        if($meta_file_content === '' || $meta_file_content !== $last_modified_hash)
        {
            $php_code = $this->compiler->compile($class_name);
            file_put_contents($this->temp_directory.'/'.$class_name.'.php', $php_code->getCode($this->compiler->getCodeFormatter()));
            file_put_contents($meta_file, $last_modified_hash);
        }
    }
}
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

use PHPComponent\AtomicFile\AtomicFileReader;
use PHPComponent\AtomicFile\AtomicFileWriter;

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
            require_once $this->getContainerFileName($class_name);
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

        $meta_file = $this->getMetaFileName($class_name);
        if(!file_exists($meta_file))
        {
            $meta_file_writer = new AtomicFileWriter($meta_file, true);
            $meta_file_writer->writeToFile('');
            $meta_file_reader = $meta_file_writer->getReader();
        }
        else
        {
            $meta_file_reader = new AtomicFileReader($meta_file);
        }

        $meta_file_content = $meta_file_reader->readFile();
        if($meta_file_content === '' || $meta_file_content !== $last_modified_hash)
        {
            $php_code = $this->compiler->compile($class_name);
            $container_file_writer = new AtomicFileWriter($this->getContainerFileName($class_name), true);
            $container_file_writer->writeToFile($php_code->getCode($this->compiler->getCodeFormatter()));
            $meta_file_writer = $meta_file_reader->getWriter();
            $meta_file_writer->writeToFile($last_modified_hash);
            $container_file_writer->closeFile();
            $meta_file_writer->closeFile();
        }
    }

    /**
     * @param string $class_name
     * @return string
     */
    private function getContainerFileName($class_name)
    {
        return $this->temp_directory.'/'.$class_name.'.php';
    }

    /**
     * @param string $class_name
     * @return string
     */
    private function getMetaFileName($class_name)
    {
        return $this->getContainerFileName($class_name).'.meta';
    }
}
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

use PHPComponent\DI\Exceptions\NonExistentServiceException;
use PHPComponent\DI\Reference\IMethodReference;
use PHPComponent\DI\Reference\IServiceReference;
use PHPComponent\DI\Reference\ServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class Helpers
{

    const OBJECTS_AS_SERVICE_REFERENCE = true;
    const OBJECTS_AS_OBJECTS = false;

    /**
     * @param object|string $class
     * @param string $class_name
     * @return bool
     */
    public static function isClassTypeOf($class, $class_name)
    {
        $reflection = new \ReflectionClass($class);
        if($reflection->getName() === ltrim($class_name, '\\')) return true;
        if($reflection->isSubclassOf($class_name)) return true;
        return false;
    }

    /**
     * @param \ReflectionMethod $reflection_method
     * @param array $arguments
     * @param IContainer $container
     * @param bool $objects_as_service_reference
     * @return array
     * @throws NonExistentServiceException
     */
    public static function autoWireArguments(\ReflectionMethod $reflection_method, array $arguments, IContainer $container, $objects_as_service_reference = self::OBJECTS_AS_OBJECTS)
    {
        $parameters = array();
        $i = 0;
        $arguments = array_values($arguments);

        $number_of_required_arguments = 0;
        $number_of_arguments = count($arguments);
        foreach($reflection_method->getParameters() as $parameter)
        {
            if(!$parameter->isDefaultValueAvailable()) $number_of_required_arguments++;
        }

        foreach($reflection_method->getParameters() as $parameter)
        {
            $parameter_class = $parameter->getClass();
            //If required argument is class and it is not passed
            if($parameter_class !== null && !array_key_exists($i, $arguments))
            {
                if($objects_as_service_reference)
                {
                    $parameters[] = self::getServiceReference($container, $parameter_class);
                }
                else
                {
                    $parameters[] = self::getService($container, $parameter_class);
                }
                continue;
            }
            //If number of required arguments is higher than passed arguments and actual passed parameter is not reference or type of.
            elseif($parameter_class !== null
                && $number_of_required_arguments >= $number_of_arguments
                && !$arguments[$i] instanceof IServiceReference
                && !$arguments[$i] instanceof IMethodReference
                && (!is_object($arguments[$i]) || !Helpers::isClassTypeOf($arguments[$i], $parameter_class->getName())))
            {
                if($objects_as_service_reference)
                {
                    $parameters[] = self::getServiceReference($container, $parameter_class);
                }
                else
                {
                    $parameters[] = self::getService($container, $parameter_class);
                }
                continue;
            }
            //If parameter has default value and argument is not passed
            elseif($parameter->isDefaultValueAvailable() && !array_key_exists($i, $arguments))
            {
                $parameters[] = $parameter->getDefaultValue();
            }
            elseif(!array_key_exists($i, $arguments))
            {
                continue;
            }
            else
            {
                $parameters[] = $arguments[$i];
            }
            $i++;
        }

        return $parameters;
    }

    /**
     * @param IContainer $container
     * @param \ReflectionClass $parameter_class
     * @return ServiceReference
     * @throws NonExistentServiceException
     */
    private static function getServiceReference(IContainer $container, \ReflectionClass $parameter_class)
    {
        $service_key = $container->getServiceKeyByClassName($parameter_class->getName());
        if($service_key === null) throw new NonExistentServiceException('Service with name '.$parameter_class->getName().' was not found. Did you register it?');
        return new ServiceReference($service_key);
    }

    /**
     * @param IContainer $container
     * @param \ReflectionClass $parameter_class
     * @return object
     * @throws NonExistentServiceException
     */
    private static function getService(IContainer $container, \ReflectionClass $parameter_class)
    {
        $service = $container->getServiceByClassName($parameter_class->getName());
        if($service === null) throw new NonExistentServiceException('Service with name '.$parameter_class->getName().' was not found. Did you register it?');
        return $service;
    }
}
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

use PHPComponent\DI\IServiceDefinition;
use PHPComponent\DI\PropertySetter;
use PHPComponent\DI\Reference\ServiceReference;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class PropertyInjectExtension extends Extension
{

    /**
     * @return void
     */
    public function beforeCompile()
    {
        $service_definitions = $this->getContainer()->getServicesDefinitions();
        foreach($service_definitions as $service_definition)
        {
            $this->updateServiceDefinition($service_definition);
        }
    }

    /**
     * @param IServiceDefinition $service_definition
     */
    private function updateServiceDefinition(IServiceDefinition $service_definition)
    {
        $reflection_class = $service_definition->getReflection();

        $properties_reflections = $reflection_class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($properties_reflections as $property_reflection)
        {
            if(preg_match('#\@di\-inject.+\@var ([a-z\\\\]+)#is', $property_reflection->getDocComment(), $matches))
            {
                $injecting_class = $matches[1];
                $service_key = $this->getContainer()->getServiceKeyByClassName($injecting_class);
                if($service_key !== null)
                {
                    $service_definition->addPropertySetter(new PropertySetter($property_reflection->getName(), new ServiceReference($service_key)));
                }
            }
        }
    }
}
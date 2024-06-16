<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Symfony\Bundle\MakerBundle\DependencyBuilder;

/**
 * @internal
 */
final class ODMDependencyBuilder
{
    /**
     * Central method to add dependencies needed for Doctrine ODM.
     */
    public static function buildDependencies(DependencyBuilder $dependencies): void
    {
        $classes = [
            // guarantee DoctrineBundle
            ManagerRegistry::class,
            // guarantee ORM
            Field::class,
        ];

        foreach ($classes as $class) {
            $dependencies->addClassDependency(
                $class,
                'doctrine/mongodb-odm-bundle'
            );
        }
    }
}

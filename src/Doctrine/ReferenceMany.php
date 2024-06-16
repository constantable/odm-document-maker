<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Symfony\Bundle\MakerBundle\Str;

/**
 * @internal
 */
final class ReferenceMany extends BaseCollectionReference
{
    public function getTargetGetterMethodName(): string
    {
        return 'get'.Str::asCamelCase($this->getTargetPropertyName());
    }

    public function getTargetSetterMethodName(): string
    {
        return 'set'.Str::asCamelCase($this->getTargetPropertyName());
    }

    public function isMapInverseRelation(): bool
    {
        throw new \Exception('OneToMany IS the inverse side!');
    }
}

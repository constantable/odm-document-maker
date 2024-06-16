<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Symfony\Bundle\MakerBundle\Str;

/**
 * @internal
 */
final class ReferenceOneToOne extends BaseReference
{
    public function getTargetGetterMethodName(): string
    {
        return 'get'.Str::asCamelCase($this->getTargetPropertyName());
    }

    public function getTargetSetterMethodName(): string
    {
        return 'set'.Str::asCamelCase($this->getTargetPropertyName());
    }
}

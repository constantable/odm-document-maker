<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Symfony\Bundle\MakerBundle\Str;

/**
 * @internal
 */
abstract class BaseCollectionReference extends BaseReference
{
    abstract public function getTargetSetterMethodName(): string;

    public function getAdderMethodName(): string
    {
        return 'add'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getPropertyName()));
    }

    public function getRemoverMethodName(): string
    {
        return 'remove'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getPropertyName()));
    }
}

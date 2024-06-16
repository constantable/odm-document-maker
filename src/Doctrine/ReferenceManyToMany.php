<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Symfony\Bundle\MakerBundle\Str;

/**
 * @internal
 */
final class ReferenceManyToMany extends BaseCollectionReference
{
    public function getTargetSetterMethodName(): string
    {
        return 'add'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getTargetPropertyName()));
    }

    public function getTargetRemoverMethodName(): string
    {
        return 'remove'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getTargetPropertyName()));
    }
}

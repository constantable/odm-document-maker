<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Symfony\Bundle\MakerBundle\Str;

/**
 * @internal
 */
abstract class BaseCollectionEmbed extends BaseEmbed
{
    public function getAdderMethodName(): string
    {
        return 'add'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getPropertyName()));
    }

    public function getRemoverMethodName(): string
    {
        return 'remove'.Str::asCamelCase(Str::pluralCamelCaseToSingular($this->getPropertyName()));
    }
}

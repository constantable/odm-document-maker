<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

/**
 * @internal
 */
abstract class BaseEmbed
{
    public function __construct(
        private string $propertyName,
        private string $targetClassName,
        private bool $isNullable = false,
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getTargetClassName(): string
    {
        return $this->targetClassName;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}

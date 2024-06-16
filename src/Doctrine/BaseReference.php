<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

/**
 * @internal
 */
abstract class BaseReference
{
    public function __construct(
        private string $propertyName,
        private string $targetDocument,
        private ?string $targetPropertyName = null,
        private bool $isSelfReferencing = false,
        private bool $mapInverseRelation = true,
        private bool $avoidSetter = false,
        private bool $isCustomReturnTypeNullable = false,
        private ?string $customReturnType = null,
        private bool $isOwning = false,
        private bool $orphanRemoval = false,
        private bool $isNullable = false,
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getTargetDocument(): string
    {
        return $this->targetDocument;
    }

    public function getTargetPropertyName(): ?string
    {
        return $this->targetPropertyName;
    }

    public function isSelfReferencing(): bool
    {
        return $this->isSelfReferencing;
    }

    public function getMapInverseRelation(): bool
    {
        return $this->mapInverseRelation;
    }

    public function shouldAvoidSetter(): bool
    {
        return $this->avoidSetter;
    }

    public function getCustomReturnType(): ?string
    {
        return $this->customReturnType;
    }

    public function isCustomReturnTypeNullable(): bool
    {
        return $this->isCustomReturnTypeNullable;
    }

    public function isOwning(): bool
    {
        return $this->isOwning;
    }

    public function getOrphanRemoval(): bool
    {
        return $this->orphanRemoval;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}

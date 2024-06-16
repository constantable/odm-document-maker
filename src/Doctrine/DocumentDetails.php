<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * @author Chigakov Konstantin <constantable@gmail.com>
 *
 * @internal
 */
final class DocumentDetails
{
    public function __construct(
        private ClassMetadata $metadata,
    ) {
    }

    public function getRepositoryClass(): ?string
    {
        return $this->metadata->customRepositoryClassName;
    }

    public function getIdentifier(): mixed
    {
        return $this->metadata->identifier[0];
    }

    public function getDisplayFields(): array
    {
        return $this->metadata->fieldMappings;
    }

    public function getFormFields(): array
    {
        $fields = (array) $this->metadata->fieldNames;
        // Remove the primary key field if it's not managed manually
        if (!$this->metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $this->metadata->identifier);
        }
        $fields = array_values($fields);

        if (!empty($this->metadata->embeddedClasses)) {
            foreach (array_keys($this->metadata->embeddedClasses) as $embeddedClassKey) {
                $fields = array_filter($fields, static fn ($v) => !str_starts_with($v, $embeddedClassKey.'.'));
            }
        }

        foreach ($this->metadata->associationMappings as $fieldName => $relation) {
            if (\Doctrine\ODM\MongoDB\Mapping\ClassMetadata::REFERENCE_MANY !== $relation['type']) {
                $fields[] = $fieldName;
            }
        }

        $fieldsWithTypes = [];
        foreach ($fields as $field) {
            $fieldsWithTypes[$field] = null;
        }

        return $fieldsWithTypes;
    }
}

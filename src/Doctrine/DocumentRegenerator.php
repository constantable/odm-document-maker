<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Constantable\OdmDocumentMaker\Util\ClassSourceManipulator;

/**
 * @internal
 */
final class DocumentRegenerator
{
    public function __construct(
        private DoctrineODMHelper $doctrineHelper,
        private FileManager $fileManager,
        private Generator $generator,
        private DocumentClassGenerator $documentClassGenerator,
        private bool $overwrite,
    ) {
    }

    public function regenerateDocuments(string $classOrNamespace): void
    {
        try {
            $metadata = $this->doctrineHelper->getMetadata($classOrNamespace);
        } catch (MappingException|PersistenceMappingException) {
            $metadata = $this->doctrineHelper->getMetadata($classOrNamespace, true);
        }

        if ($metadata instanceof ClassMetadata) {
            $metadata = [$metadata];
        } elseif (class_exists($classOrNamespace)) {
            throw new RuntimeCommandException(sprintf('Could not find Doctrine metadata for "%s". Is it mapped as a document?', $classOrNamespace));
        } elseif (empty($metadata)) {
            throw new RuntimeCommandException(sprintf('No documents were found in the "%s" namespace.', $classOrNamespace));
        }

        /** @var ClassSourceManipulator[] $operations */
        $operations = [];
        foreach ($metadata as $classMetadata) {
            if (!class_exists($classMetadata->name)) {
                // the class needs to be generated for the first time!
                $classPath = $this->generateClass($classMetadata);
            } else {
                $classPath = $this->getPathOfClass($classMetadata->name);
            }

            $mappedFields = $this->getMappedFieldsInDocument($classMetadata);

            if ($classMetadata->customRepositoryClassName) {
                $this->generateRepository($classMetadata);
            }

            $manipulator = $this->createClassManipulator($classPath);
            $operations[$classPath] = $manipulator;

            foreach ($classMetadata->getEmbeddedFieldsMappings() as $fieldName => $mapping) {
                $className = $mapping['targetDocument'];

                if (!\in_array($fieldName, $mappedFields)) {
                    continue;
                }
                if ($classMetadata->isSingleValuedEmbed($fieldName)) {
                    $manipulator->addEmbedOne(new EmbedOne($fieldName, $className));
                } else {
                    $manipulator->addEmbedMany(new EmbedMany($fieldName, $className));
                }
            }

            foreach ($classMetadata->fieldMappings as $fieldName => $mapping) {
                if (!\in_array($fieldName, $mappedFields)) {
                    continue;
                }

                $manipulator->addDocumentField($fieldName, $mapping);
            }

            $getIsNullable = function (array $mapping) {
                return true;
            };

            foreach ($classMetadata->associationMappings as $fieldName => $mapping) {
                if (!\in_array($fieldName, $mappedFields)) {
                    continue;
                }
                if (@$mapping['embedded']) {
                    continue;
                }
                switch ($mapping['type']) {
                    case ClassMetadata::ONE:
                        $relation = (new ReferenceOne(
                            propertyName: $mapping['fieldName'],
                            targetDocument: $mapping['targetDocument'],
                            targetPropertyName: @$mapping['inversedBy'],
                            mapInverseRelation: null !== @$mapping['inversedBy'],
                            isOwning: true,
                            isNullable: $getIsNullable($mapping),
                        ));

                        $manipulator->addManyToOneReference($relation);

                        break;
                    case ClassMetadata::MANY:
                        $relation = (new ReferenceMany(
                            propertyName: $mapping['fieldName'],
                            targetDocument: $mapping['targetDocument'],
                            targetPropertyName: $mapping['mappedBy'],
                            orphanRemoval: $mapping['orphanRemoval'],
                        ));

                        $manipulator->addOneToManyReference($relation);

                        break;

                    default:
                        throw new \Exception('Unknown association type. ');
                }
            }
        }

        foreach ($operations as $filename => $manipulator) {
            $this->fileManager->dumpFile(
                $filename,
                $manipulator->getSourceCode()
            );
        }
    }

    private function generateClass(ClassMetadata $metadata): string
    {
        $path = $this->generator->generateClass(
            $metadata->name,
            'Class.tpl.php',
            []
        );
        $this->generator->writeChanges();

        return $path;
    }

    private function createClassManipulator(string $classPath): ClassSourceManipulator
    {
        return new ClassSourceManipulator(
            sourceCode: $this->fileManager->getFileContents($classPath),
            overwrite: $this->overwrite,
            // if properties need to be generated then, by definition,
            // some non-annotation config is being used (e.g. XML), and so, the
            // properties should not have annotations added to them
            useAttributesForDoctrineMapping: false
        );
    }

    private function getPathOfClass(string $class): string
    {
        return (new \ReflectionClass($class))->getFileName();
    }

    private function generateRepository(ClassMetadata $metadata): void
    {
        if (!$metadata->customRepositoryClassName) {
            return;
        }

        if (class_exists($metadata->customRepositoryClassName)) {
            // repository already exists
            return;
        }

        $this->documentClassGenerator->generateRepositoryClass(
            $metadata->customRepositoryClassName,
            $metadata->name,
            false
        );

        $this->generator->writeChanges();
    }

    private function getMappedFieldsInDocument(ClassMetadata $classMetadata): array
    {
        /** @var \ReflectionClass $classReflection */
        $classReflection = $classMetadata->reflClass;

        $targetFields = [
            ...array_keys($classMetadata->fieldMappings),
            ...array_keys($classMetadata->associationMappings),
        ];

        if ($classReflection) {
            // exclude traits
            $traitProperties = [];

            foreach ($classReflection->getTraits() as $trait) {
                foreach ($trait->getProperties() as $property) {
                    $traitProperties[] = $property->getName();
                }
            }

            $targetFields = array_diff($targetFields, $traitProperties);

            // exclude inherited properties
            $targetFields = array_filter($targetFields, static fn ($field) => $classReflection->hasProperty($field)
                && $classReflection->getProperty($field)->getDeclaringClass()->getName() === $classReflection->getName());
        }

        return $targetFields;
    }
}

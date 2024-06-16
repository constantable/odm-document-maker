<?php

namespace Constantable\OdmDocumentMaker\Doctrine;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;

/**
 * @internal
 *
 * @author Chigakov Konstantin <constantable@gmail.com>
 */
final class DocumentClassGenerator
{
    public function __construct(
        private Generator $generator,
        private DoctrineODMHelper $doctrineHelper,
    ) {
    }

    public function generateDocumentClass(ClassNameDetails $documentClassDetails, bool $apiResource, bool $generateEmbeddedDocument = false): string
    {
        $collectionName = $this->doctrineHelper->getPotentialCollectionName($documentClassDetails->getFullName());

        $repoClassDetails = $this->generator->createClassNameDetails(
            $documentClassDetails->getRelativeName(),
            'Repository\\',
            'Repository'
        );

        $useStatements = new UseStatementGenerator([
            ['Doctrine\\ODM\\MongoDB\\Mapping\\Annotations' => 'ODM'],
        ]);

        if (!$generateEmbeddedDocument) {
            $useStatements->addUseStatement($repoClassDetails->getFullName());
        }

        if ($apiResource) {
            $useStatements->addUseStatement(ApiResource::class);
        }

        $documentPath = $this->generator->generateClass(
            $documentClassDetails->getFullName(),
            $generateEmbeddedDocument ? __DIR__.'/../Resources/skeleton/doctrine/EmbeddedDocument.tpl.php' : __DIR__.'/../Resources/skeleton/doctrine/Document.tpl.php',
            [
                'use_statements' => $useStatements,
                'repository_class_name' => $repoClassDetails->getShortName(),
                'api_resource' => $apiResource,
                'collection_name' => $collectionName,
                'embedded' => $generateEmbeddedDocument,
            ]
        );

        if (!$generateEmbeddedDocument) {
            $this->generateRepositoryClass(
                $repoClassDetails->getFullName(),
                $documentClassDetails->getFullName(),
                true
            );
        }

        return $documentPath;
    }

    public function generateRepositoryClass(string $repositoryClass, string $documentClass, bool $includeExampleComments = true): void
    {
        $shortDocumentClass = Str::getShortClassName($documentClass);
        $documentAlias = strtolower($shortDocumentClass[0]);

        $useStatements = new UseStatementGenerator([
            $documentClass,
            DocumentManager::class,
            DocumentRepository::class,
        ]);

        $this->generator->generateClass(
            $repositoryClass,
            __DIR__.'/../Resources/skeleton/doctrine/DocumentRepository.tpl.php',
            [
                'use_statements' => $useStatements,
                'document_class_name' => $shortDocumentClass,
                'document_alias' => $documentAlias,
                'include_example_comments' => $includeExampleComments,
            ]
        );
    }
}

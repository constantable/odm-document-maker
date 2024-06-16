<?php

namespace Constantable\OdmDocumentMaker\Tests\tmp\current_project_xml\src\DocumentRepository;

use Constantable\OdmDocumentMaker\Tests\tmp\current_project_xml\src\Document\UserXml;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * @extends DocumentRepository<UserXml>
 *
 * @method UserXml|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserXml|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserXml[]    findAll()
 * @method UserXml[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(UserXml::class);
        parent::__construct($dm, $uow, $classMetaData);
    }
}

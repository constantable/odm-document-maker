<?php

namespace Constantable\OdmDocumentMaker\Tests\tmp\current_project\src\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

#[ODM\Document]
class UserAvatar
{
    #[ODM\Id]
    private ?int $id = null;

    #[ODM\ReferenceOne(targetDocument: User::class, cascade: ['persist', 'remove'], inversedBy: 'avatars')]
    private ?User $user = null;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): static
    {
        $this->user = $user;

        return $this;
    }
}

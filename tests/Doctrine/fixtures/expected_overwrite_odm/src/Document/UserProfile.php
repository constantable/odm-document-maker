<?php

namespace Constantable\OdmDocumentMaker\Tests\tmp\current_project\src\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

#[ODM\Document]
class UserProfile
{
    #[ODM\Id]

    private ?int $id = null;

    #[ODM\ReferenceOne(targetDocument: User::class, cascade: ['persist', 'remove'], inversedBy: 'userProfile')]
    private ?User $user = null;

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}

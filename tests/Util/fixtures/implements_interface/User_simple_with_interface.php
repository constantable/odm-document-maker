<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ODM\Document]
class User implements DummyInterface, UserInterface
{
    #[ODM\Id]
    #[ODM\GeneratedValue]
    #[ODM\Field]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

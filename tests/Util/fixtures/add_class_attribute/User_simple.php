<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;

#[ODM\Document]
#[Field(message: 'We use this attribute for class level tests so we dont have to add additional test dependencies.')]
class User
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

<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class User
{
    #[ODM\Id]
    #[ODM\GeneratedValue]
    #[ODM\Field]
    private ?int $id = null;

    #[ODM\Field]
    private ?string $firstName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Some custom comments
     *
     * @return string
     */
    public function getFirstName()
    {
        // some custom comment
        return $this->firstName;
    }
}

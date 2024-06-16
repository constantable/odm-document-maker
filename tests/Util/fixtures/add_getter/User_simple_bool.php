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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFooProp(): ?bool
    {
        return $this->fooProp;
    }
}

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

    public function __construct(object $someObjectParam, string $someStringParam)
    {
        $this->someObjectParam = $someObjectParam;
        $this->someMethod($someStringParam);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

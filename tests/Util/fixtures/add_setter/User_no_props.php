<?php

namespace App\Document;

use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(repositoryClass: UserRepository::class)]
class User
// extra space to keep things interesting
{
    public function hello()
    {
        return 'hi there!';
    }

    /**
     * @param string $fooProp
     * @internal
     */
    public function setFooProp(?string $fooProp): static
    {
        $this->fooProp = $fooProp;

        return $this;
    }
}

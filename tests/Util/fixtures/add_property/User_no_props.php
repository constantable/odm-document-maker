<?php

namespace App\Document;

use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(repositoryClass: UserRepository::class)]
class User
// extra space to keep things interesting
{
    /**
     * @var string
     * @internal
     */
    private $fooProp;

    public function hello()
    {
        return 'hi there!';
    }
}

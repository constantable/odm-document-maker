<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class User
{
    const FOO = 'bar';

    /**
     * Hi!
     */
    const BAR = 'bar';

    private $fooProp;

    /**
     * @return string
     */
    public function hello()
    {
        return 'hi there!';
    }
}

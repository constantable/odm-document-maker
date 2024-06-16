<?php

namespace App\Document;

class User
{
    const CONSTANTE = "testConst";

    public function __construct(object $someObjectParam, string $someStringParam)
    {
        $this->someObjectParam = $someObjectParam;
        $this->someMethod($someStringParam);
    }
}

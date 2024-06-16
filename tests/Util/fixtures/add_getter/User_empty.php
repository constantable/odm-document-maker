<?php

namespace App\Document;

class User
{
    public function getFooProp(): ?string
    {
        return $this->fooProp;
    }
}

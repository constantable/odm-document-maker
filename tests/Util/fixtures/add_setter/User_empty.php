<?php

namespace App\Document;

class User
{
    public function setFooProp(string $fooProp): static
    {
        $this->fooProp = $fooProp;

        return $this;
    }
}

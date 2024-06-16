<?php

namespace App\Document;

class User
{
    /**
     * test comment on public method
     */
    public function testAddNewMethod(string $someParam): ?string
    {
        $this->someParam = $someParam;
    }
}

<?php

namespace App\Enums\Concerns;

trait Label
{
    public function getLabel(): string
    {
        return $this->value;
    }
}

<?php

namespace App\Exceptions;

use App\Enums\PostType;
use RuntimeException;

class InvalidPostTypeException extends RuntimeException
{
    public function __construct(PostType $expect, PostType $actual)
    {
        parent::__construct("Unexpected post type: [$actual->name], Expected [$expect->name]");
    }
}

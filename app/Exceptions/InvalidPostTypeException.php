<?php

namespace App\Exceptions;

use App\Enums\PostType;
use RuntimeException;

class InvalidPostTypeException extends RuntimeException
{
    public function __construct(PostType $expect, ?PostType $actual)
    {
        $unexpected = $actual?->name ?? 'null';

        parent::__construct("Unexpected post type: [$unexpected], Expected [$expect->name]");
    }
}

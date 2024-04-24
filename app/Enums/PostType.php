<?php

namespace App\Enums;

enum PostType: string
{
    case Article    = 'artikel';
    case Assignment = 'tugas';
    case Link       = 'link';
}

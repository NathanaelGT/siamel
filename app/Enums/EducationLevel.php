<?php

namespace App\Enums;

enum EducationLevel: string
{
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case D3 = 'D3';
    case D4 = 'D4';

    public function getId(): int
    {
        return match ($this) {
            self::D3           => 0,
            self::D4, self::S1 => 1,
            self::S2           => 2,
            self::S3           => 3,
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssignmentCategory: string implements HasLabel
{
    use Concerns\Label;

    case Homework = 'Tugas';
    case Quiz     = 'Quiz';
    case Project  = 'Projek';
    case Midterm  = 'UTS';
    case Final    = 'UAS';
}

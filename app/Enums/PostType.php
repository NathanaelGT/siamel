<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PostType: string implements HasLabel
{
    use Concerns\Label;

    case LearningMaterial = 'Materi';
    case Assignment       = 'Tugas';
}

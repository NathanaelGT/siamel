<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssignmentType: string implements HasLabel
{
    use Concerns\Label;

    case Individual = 'Individu';
    case Group      = 'Kelompok';
}

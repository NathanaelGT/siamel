<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkingDay: string implements HasLabel
{
    use Concerns\Label;

    case Monday    = 'Senin';
    case Tuesday   = 'Selasa';
    case Wednesday = 'Rabu';
    case Thursday  = 'Kamis';
    case Friday    = 'Jumat';
}

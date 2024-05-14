<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    use Concerns\Label;

    case Male   = 'Laki-laki';
    case Female = 'Perempuan';
}

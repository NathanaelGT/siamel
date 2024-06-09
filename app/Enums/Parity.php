<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Parity: string implements HasLabel
{
    use Concerns\Label;

    case Even = 'Genap';
    case Odd  = 'Ganjil';
}

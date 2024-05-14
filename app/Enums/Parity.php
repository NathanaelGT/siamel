<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Parity: string implements HasLabel
{
    use Concerns\Label;

    case Odd  = 'Ganjil';
    case Even = 'Genap';
}

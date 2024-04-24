<?php

namespace App\Enums;

enum Parity: string
{
    case Odd  = 'Ganjil';
    case Even = 'Genap';
    case Null = 'Tidak Ada';
}

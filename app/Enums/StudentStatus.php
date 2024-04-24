<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active   = 'Aktif';
    case Graduted = 'Lulus';
    case Dropout  = 'DO';
    case Leave    = 'Cuti';
}

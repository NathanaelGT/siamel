<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active   = 'Aktif';
    case Inactive = 'Tidak Aktif';
    case Leave    = 'Cuti';
}

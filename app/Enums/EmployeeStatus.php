<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmployeeStatus: string implements HasLabel
{
    use Concerns\Label;

    case Active   = 'Aktif';
    case Inactive = 'Tidak Aktif';
    case Leave    = 'Cuti';

    public static function badgeColor(EmployeeStatus $state): string
    {
        return match ($state) {
            EmployeeStatus::Active   => 'success',
            EmployeeStatus::Inactive => 'warning',
            EmployeeStatus::Leave    => 'gray',
        };
    }
}

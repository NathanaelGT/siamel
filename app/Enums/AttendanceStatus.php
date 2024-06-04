<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasLabel, HasColor
{
    use Concerns\Label;

    case Present = 'Hadir';
    case Sick    = 'Sakit';
    case Permit  = 'Izin';
    case Absent  = 'Alpa';

    public function getColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Sick    => 'warning',
            self::Permit  => 'gray',
            self::Absent  => 'danger',
        };
    }
}

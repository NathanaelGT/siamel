<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StudentStatus: string implements HasLabel
{
    use Concerns\Label;

    case Active    = 'Aktif';
    case Inactive  = 'Tidak Aktif';
    case Graduated = 'Lulus';
    case DropOut   = 'DO';
    case Resign    = 'Mengundurkan Diri';
    case Leave     = 'Cuti';

    public static function badgeColor(StudentStatus $state): string
    {
        return match ($state) {
            StudentStatus::Active                         => 'success',
            StudentStatus::Inactive                       => 'warning',
            StudentStatus::Graduated                      => 'info',
            StudentStatus::DropOut, StudentStatus::Resign => 'danger',
            StudentStatus::Leave                          => 'gray',
        };
    }
}

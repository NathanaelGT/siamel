<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SemesterSchedules: string implements HasLabel
{
    use Concerns\Label;

    case Normal           = 'Perkuliahan';
    case KRS              = 'KRS';
    case Midterm          = 'UTS';
    case Final            = 'UAS';
    case CommunityService = 'KKN';
    case Graduation       = 'Wisuda';
}

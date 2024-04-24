<?php

namespace App\Enums;

enum AssignmentCategory: string
{
    case Homework = 'tugas';
    case Quiz     = 'quiz';
    case Project  = 'projek';
    case Midterm  = 'UTS';
    case Final    = 'UAS';
}

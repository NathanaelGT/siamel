<?php

namespace App\Service\Subject;

use App\Enums\Parity;
use Illuminate\Support\Str;

abstract class Slug
{
    public static function generate(
        string $courseName,
        Parity $semesterParity,
        int $semesterYear,
        string $parallel,
        string $code
    ): string
    {
        $course = Str::slug($courseName, language: null, dictionary: []);
        $parity = lcfirst($semesterParity->value);
        $parallel = strtolower($parallel);

        if ($semesterParity === Parity::Odd) {
            $startYear = $semesterYear;
            $endYear = $semesterYear + 1;
        } else {
            $startYear = $semesterYear - 1;
            $endYear = $semesterYear;
        }

        return "$course-$parallel$code-semester-$parity-$startYear-$endYear";
    }
}

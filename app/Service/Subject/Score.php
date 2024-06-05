<?php

namespace App\Service\Subject;

abstract class Score
{
    public static function scoreToWeight(int $score): int
    {
        return match (true) {
            $score >= 80 => 400,
            $score >= 76 => 375,
            $score >= 72 => 350,
            $score >= 68 => 300,
            $score >= 64 => 275,
            $score >= 58 => 250,
            $score >= 56 => 200,
            $score >= 46 => 150,
            $score >= 42 => 100,
            default      => 0,
        };
    }

    public static function weightToLetter(int $weight): string
    {
        return match (true) {
            $weight >= 400 => 'A',
            $weight >= 375 => 'A-',
            $weight >= 350 => 'B+',
            $weight >= 300 => 'B',
            $weight >= 275 => 'B-',
            $weight >= 250 => 'C+',
            $weight >= 200 => 'C',
            $weight >= 150 => 'D+',
            $weight >= 100 => 'D',
            $weight >= 0   => 'E',
        };
    }
}

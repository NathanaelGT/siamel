<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\AttendanceResource\Summarizers;

use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Support\HtmlString;

class AttendanceSummarizer extends Summarizer
{
    public static float $attendance = 0;

    public function getState(): string | HtmlString
    {
        $attendance = round(static::$attendance / $this->getTable()->getRecords()->count());

        if ($attendance < 80) {
            $rgb = Color::Red[600];

            return new HtmlString(
                "<span style=\"color:rgb($rgb)\">$attendance%</span>"
            );
        }

        return $attendance . '%';
    }
}

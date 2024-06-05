<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Summarizers;

use Filament\Tables\Columns\Summarizers\Summarizer;

class CreditSummarizer extends Summarizer
{
    public static ?int $value = null;

    public function getState(): mixed
    {
        return static::$value ??= $this->getTable()->getRecords()->sum('course.credits');
    }
}

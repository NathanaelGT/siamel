<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Summarizers;

use Filament\Tables\Columns\Summarizers\Summarizer;

class NxKSummarizer extends Summarizer
{
    public static ?float $value = null;

    public function getState(): mixed
    {
        return static::$value ??= $this->getTable()->getRecords()->sum('nxk');
    }
}

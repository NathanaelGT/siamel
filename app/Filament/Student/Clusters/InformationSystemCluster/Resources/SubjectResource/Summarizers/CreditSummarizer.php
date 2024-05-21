<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Summarizers;

use Filament\Tables\Columns\Summarizers\Summarizer;

class CreditSummarizer extends Summarizer
{
    public function getState(): mixed
    {
        return $this->getTable()->getRecords()->sum('course.credits');
    }
}

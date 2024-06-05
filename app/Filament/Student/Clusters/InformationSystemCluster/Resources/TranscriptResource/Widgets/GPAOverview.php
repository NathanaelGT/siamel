<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Widgets;

use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Summarizers\CreditSummarizer;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Summarizers\NxKSummarizer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GPAOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $ip = NxKSummarizer::$value === null || CreditSummarizer::$value === null
            ? '-'
            : number_format(NxKSummarizer::$value / CreditSummarizer::$value, 2);

        return [
            Stat::make('Indeks Prestasi', $ip),

            Stat::make('SKS Kumulatif', CreditSummarizer::$value ?? '-'),

            Stat::make('Semester', TranscriptResource::$highestSemester ?: '-'),
        ];
    }
}

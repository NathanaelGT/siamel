<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Widgets;

use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource;
use App\Filament\Tables\Summarizer\LocalSum;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GPAOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalNxk = LocalSum::instance('nxk')?->getTotalValue();
        $totalCredits = LocalSum::instance('course.credits')?->getTotalValue();

        $ip = ! $totalNxk || ! $totalCredits
            ? '-'
            : number_format($totalNxk / $totalCredits, 2);

        return [
            Stat::make('Indeks Prestasi', $ip),

            Stat::make('SKS Kumulatif', $totalCredits ?? '-'),

            Stat::make('Semester', TranscriptResource::$highestSemester ?: '-'),
        ];
    }
}

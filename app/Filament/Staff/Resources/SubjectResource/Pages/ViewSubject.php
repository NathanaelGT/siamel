<?php

namespace App\Filament\Staff\Resources\SubjectResource\Pages;

use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Subject;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSubject extends ViewRecord
{
    protected static string $resource = SubjectResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make([
                Infolists\Components\TextEntry::make('course.name')
                    ->formatStateUsing(function (Subject $record) {
                        return "{$record->course->name} {$record->parallel}{$record->code}";
                    }),

                Infolists\Components\TextEntry::make('professor.account.name')
                    ->label('Dosen'),

                Infolists\Components\TextEntry::make('room.name')
                    ->label('Tempat')
                    ->formatStateUsing(function (Subject $record) {
                        return "{$record->room->building->abbreviation} {$record->room->name}, {$record->capacity} orang";
                    }),

                Infolists\Components\TextEntry::make('day')
                    ->label('Waktu')
                    ->formatStateUsing(function (Subject $record) {
                        $start = $record->start_time->format('H:i');
                        $end = $record->end_time->format('H:i');

                        return "{$record->day->value}, Pukul {$start}-{$end}";
                    }),
            ])->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

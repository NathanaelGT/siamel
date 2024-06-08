<?php

namespace App\Filament\Student\Widgets;

use App\Filament\Student\Resources\SubjectResource;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;

class TodaySubjectTable extends BaseWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Kelas Hari Ini')
            ->emptyStateHeading('Tidak ada kelas hari ini')
            ->columns([
                Tables\Columns\Layout\Grid::make()->schema([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('subject.course.name')
                            ->formatStateUsing(fn(string $state) => new HtmlString('<strong>' . e($state) . '</strong>'))
                            ->url(fn(SubjectSchedule $record) => SubjectResource::getUrl('view', [$record->subject])),

                        Tables\Columns\TextColumn::make('meeting_no')
                            ->prefix('Pertemuan ke-'),
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('null')
                            ->default(new HtmlString('<br>')),

                        Tables\Columns\TextColumn::make('subject.room.full_name'),

                        Tables\Columns\TextColumn::make('time')
                            ->prefix('Pukul ')
                            ->tooltip(fn(SubjectSchedule $record) => $record->start_time->diffForHumans(parts: 7)),
                    ]),
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(SubjectSchedule $record) => SubjectResource::getUrl('view', [$record->subject])),
            ])
            ->query(function () {
                return SubjectSchedule::query()
                    ->with(['subject' => ['course', 'room']])
                    ->whereIn('subject_id', Subject::query()
                        ->select(['id'])
                        ->where('semester_id', Semester::current()->id)
                        ->whereStudent(auth()->user()->info_id))
                    ->whereDate('start_time', today())
                    ->orderBy('start_time');
            });
    }
}

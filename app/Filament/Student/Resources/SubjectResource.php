<?php

namespace App\Filament\Student\Resources;

use App\Filament\Resource;
use App\Filament\Student\Resources\SubjectResource\Pages;
use App\Models\Semester;
use App\Models\Subject;
use App\Period\Period;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        $semesters = Semester::query()
            ->orderBy('id', 'desc')
            ->when(Gate::check(Period::Learning), function (Builder $query) {
                $query->where('id', '<=', Semester::current()->id)
                    ->limit(auth()->user()->student->semester);
            }, function (Builder $query) {
                $query->where('id', '<', Semester::current()->id)
                    ->limit(auth()->user()->student->semester - 1);
            })
            ->pluck('academic_year', 'id')
            ->map(fn(string $label) => substr($label, 9))
            ->all();

        $defaultSemester = Arr::firstKey($semesters);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parallel')
                    ->formatStateUsing(fn(Subject $record) => implode([
                        $record->parallel,
                        $record->code,
                    ]))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('professor.name')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('room.name')
                    ->formatStateUsing(fn(Subject $record) => $record->room->full_name)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('day')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->sortable()
                    ->formatStateUsing(fn(Subject $record) => implode(' - ', [
                        $record->start_time->format('H:i'),
                        $record->end_time->format('H:i'),
                    ]))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('semester.academic_year')
                    ->sortable(query: function (Builder $query, string $direction = 'asc') {
                        $query->orderBy('semester_id', $direction);
                    })
//                    ->hidden(fn() => $table->getFilter('semester_id')->getState()['values'] === $defaultSemester)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->native(false)
                    ->default($defaultSemester)
                    ->options($semesters)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return Subject::query()
            ->whereStudent(auth()->user()->info_id)
            ->when(Gate::check(Period::Learning), function (Builder $query) {
                $query->where('semester_id', '<=', Semester::current()->id);
            }, function (Builder $query) {
                $query->where('semester_id', '<', Semester::current()->id);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubjects::route('/'),
            'view'   => Pages\ViewSubject::route('/{record}'),
            'post'   => Pages\PostDetail::route('/{record}/{postId}'),
            'upload' => Pages\UploadSubmission::route('/{subject}/{post}/upload'),
        ];
    }
}

<?php

namespace App\Filament\Staff\Resources\CourseResource\RelationManagers;

use App\Enums\WorkingDay;
use App\Filament\RelationManager;
use App\Filament\Staff\Resources\SubjectResource;
use App\Filament\Tables\Columns\AvatarColumn;
use App\Models\Room;
use App\Models\Semester;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('course.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->paginated(false)
            ->groups([
                Tables\Grouping\Group::make('professor.account.name')
                    ->label('Dosen'),

                Tables\Grouping\Group::make('room.building.abbreviation')
                    ->label('Gedung')
                    ->orderQueryUsing(function (Tables\Grouping\Group $group, Builder $query, string $direction) {
                        $query1 = invade($group)->getSortColumnForQuery($query, $group->getRelationshipAttribute());
                        $query2 = Room::query()
                            ->select('name')
                            ->whereColumn('subjects.room_id', 'rooms.id');

                        $query->orderBy($query1, $direction)
                            ->orderBy($query2, $direction)
                            ->orderBy('parallel', $direction);
                    })
                    ->getTitleFromRecordUsing(fn(Subject $record) => $record->room->building->name),
            ])
            ->columns([
                AvatarColumn::make('professor.account.avatar_url')
                    ->toggleable()
                    ->resolveAccountUsing(fn(Subject $record) => $record->professor->account),

                Tables\Columns\TextColumn::make('professor.account.name')
                    ->label('Dosen'),

                Tables\Columns\TextColumn::make('title')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('parallel', $direction);
                    }),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Kapasitas')
                    ->formatStateUsing(function (Subject $record) {
                        return "$record->students_count/$record->capacity";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('room.full_name'),

                Tables\Columns\TextColumn::make('day')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderByRaw("field(`day`, ?, ?, ?, ?, ?) $direction", WorkingDay::cases())
                            ->orderBy('start_time', $direction)
                            ->orderBy('parallel', $direction);
                    }),

                Tables\Columns\TextColumn::make('time'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->native(false)
                    ->default(Semester::current()->id)
                    ->options(fn() => once(function () {
                        return Semester::pluck('academic_year', 'id')->map(function (string $academicYear) {
                            return substr($academicYear, 9);
                        });
                    })),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->url(SubjectResource::getUrl('create')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Subject $subject) => SubjectResource::getUrl('view', [$subject])),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['course', 'room.building'])
                    ->withCount('students');
            });
    }
}

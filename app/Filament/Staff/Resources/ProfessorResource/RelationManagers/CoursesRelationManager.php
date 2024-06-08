<?php

namespace App\Filament\Staff\Resources\ProfessorResource\RelationManagers;

use App\Models\Course;
use App\Models\StudyProgram;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** @property-read \App\Models\Professor $ownerRecord */
class CoursesRelationManager extends RelationManager
{
    protected static ?string $title = 'Bidang Keahlian';

    protected static string $relationship = 'courses';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),

                Tables\Columns\TextColumn::make('studyProgram.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->multiple()
                    ->label('Tambahkan')
                    ->modalHeading('Tambahkan keahlian')
                    ->modalSubmitActionLabel('Tambahkan')
                    ->attachAnother(false)
                    ->preloadRecordSelect()
                    ->successNotificationTitle('Bidang keahlian berhasil ditambahkan')
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $query->whereIn(
                            'study_program_id',
                            StudyProgram::query()
                                ->where('faculty_id', $this->ownerRecord->faculty_id)
                                ->select('id')
                        );
                    })
                    ->recordTitle(function (Course $record) {
                        return $record->name . ' (' . $record->studyProgram->name . ')';
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Hapus bidang')
                    ->modalHeading(fn(Course $record) => "Hapus bidang keahlian $record->name")
                    ->successNotificationTitle('Bidang keahlian berhasil dihapus'),
            ])
            ->bulkActions([
                //
            ]);
    }
}

<?php

namespace App\Filament\Staff\Resources\FacultyResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Staff\Resources\StudyProgramResource;
use App\Models\StudyProgram;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudyProgramsRelationManager extends RelationManager
{
    protected static string $relationship = 'studyPrograms';

    public function form(Form $form): Form
    {
        return StudyProgramResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StudyProgramResource::table($table)
            ->paginated(false)
            ->recordTitleAttribute('name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $data['faculty_id'] = $this->ownerRecord->id;

                        return StudyProgram::create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(StudyProgram $record) => StudyProgramResource::getUrl('view', [$record])),

                Tables\Actions\EditAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['faculty:id,slug']);
            });
    }
}

<?php

namespace App\Filament\Staff\Resources\StudyProgramResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Staff\Resources\CourseResource;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    public function form(Form $form): Form
    {
        return CourseResource::form($form);
    }

    public function table(Table $table): Table
    {
        return CourseResource::table($table)
            ->recordTitleAttribute('name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $data['study_program_id'] = $this->ownerRecord->id;

                        return Course::query()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Course $record) => CourseResource::getUrl('view', [$record])),

                Tables\Actions\EditAction::make(),
            ]);
    }
}

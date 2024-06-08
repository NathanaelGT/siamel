<?php

namespace App\Filament\Staff\Resources\StudyProgramResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Staff\Resources\StudentResource;
use App\Models\Student;
use App\Service\Auth\CreateAccount;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return StudentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StudentResource::table($table)
            ->recordTitleAttribute('account.name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $data['study_program_id'] = $this->ownerRecord->id;

                        return CreateAccount::student($data);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Student $record) => StudentResource::getUrl('view', [$record])),

                Tables\Actions\EditAction::make()
                    ->url(fn(Student $record) => StudentResource::getUrl('edit', [$record])),
            ]);
    }
}

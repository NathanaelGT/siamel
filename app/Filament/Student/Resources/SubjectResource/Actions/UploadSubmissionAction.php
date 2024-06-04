<?php

namespace App\Filament\Student\Resources\SubjectResource\Actions;

use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;

class UploadSubmissionAction extends Action
{
    protected Subject $subject;

    public function subject(Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public static function getDefaultName(): ?string
    {
        return 'uploadSubmission';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Kumpulkan');
        $this->modalHeading('Kumpulkan tugas');
        $this->modalSubmitActionLabel('Kumpul');
        $this->successNotificationTitle('Tugas berhasil dikumpul');
        $this->closeModalByClickingAway(false);

        $this->form(function (Form $form) {
            return $form->schema([
                Forms\Components\FileUpload::make('attachments')
                    ->label('Berkas')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->openable()
                    ->disk('local')
                    ->directory(fn() => 'subject/' . $this->subject->slug . '/students')
                    ->visibility('private')
                    ->storeFileNamesIn('attachment_file_names'),
            ]);
        });

        $this->action(function (array $data) {
            dd($data);
        });
    }
}

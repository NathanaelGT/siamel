<?php

namespace App\Filament\Professor\Resources\SubjectResource\Actions;

use App\Enums\AssignmentCategory;
use App\Enums\AssignmentType;
use App\Enums\PostType;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class CreateAssignmentAction extends Action
{
    protected Subject $subject;

    public function subject(Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public static function getDefaultName(): ?string
    {
        return 'createAssignment';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Tugas Baru');
        $this->modalHeading('Unggah tugas baru');
        $this->modalSubmitActionLabel('Unggah');
        $this->successNotificationTitle('Tugas berhasil diunggah');
        $this->closeModalByClickingAway(false);

        $this->form(function (Form $form) {
            /** @var \Illuminate\Support\Collection $meetings */
            $meetings = once(fn() => $this->subject
                ->schedules()
                ->where('end_time', '>', now()->subWeek())
                ->pluck('start_time', 'meeting_no'));

            return $form->columns(1)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Grid::make()->schema([
                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->native(false)
                        ->default(AssignmentType::Individual->value)
                        ->options(AssignmentType::class)
                        ->required(),

                    Forms\Components\Select::make('category')
                        ->label('Kategori')
                        ->native(false)
                        ->default(AssignmentCategory::Homework->value)
                        ->options(AssignmentCategory::class)
                        ->required(),

                    Forms\Components\Select::make('published_at')
                        ->label('Terbitkan pada')
                        ->native(false)
                        ->default('now')
                        ->options($meetings
                            ->take(5)
                            ->mapWithKeys(fn(Carbon $startTime, int $meetingNo) => [
                                $startTime->toDateTimeString() => "Pertemuan $meetingNo",
                            ])
                            ->prepend('Sekarang', 'now')
                            ->all())
                        ->required(),

                    Forms\Components\Select::make('deadline')
                        ->label('Tenggat')
                        ->native(false)
                        ->options(
                            $meetings->mapWithKeys(fn(Carbon $startTime, int $meetingNo) => [
                                $startTime->startOfDay()->subMicrosecond()->toDateTimeString() => "Pertemuan $meetingNo",
                            ])->all()
                        )
                        ->default($meetings->skip(1)->first()?->toDateTimeString())
                        ->required(),
                ]),

                Forms\Components\RichEditor::make('content')
                    ->label('Isi')
                    ->disableToolbarButtons(['attachFiles']),

                Forms\Components\FileUpload::make('attachments')
                    ->label('Lampiran')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->openable()
                    ->disk('local')
                    ->directory(fn() => 'subject/' . $this->subject->slug)
                    ->visibility('private')
                    ->storeFileNamesIn('attachment_file_names'),
            ]);
        });

        $this->action(function (array $data, Form $form, HasTable $livewire) {
            $record = $this->subject->posts()->create(['type' => PostType::Assignment] + $data);
            $record->assignment()->create(['mimes' => ''] + $data);

            $ownerId = auth()->id();

            $record->attachments()->createMany(
                Arr::map($data['attachment_file_names'], fn(string $originalName, string $storedPath) => [
                    'owner_id' => $ownerId,
                    'name'     => $originalName,
                    'path'     => $storedPath,
                    'slug'     => $storedPath,
                ])
            );

            $this->record($record);
            $form->model($record)->saveRelationships();

            $livewire->mountedTableActionRecord($record->getKey());

            $this->success();
        });
    }
}

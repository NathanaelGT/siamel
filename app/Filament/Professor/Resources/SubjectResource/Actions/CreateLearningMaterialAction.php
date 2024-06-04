<?php

namespace App\Filament\Professor\Resources\SubjectResource\Actions;

use App\Enums\PostType;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Malzariey\FilamentDaterangepickerFilter\Enums\DropDirection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class CreateLearningMaterialAction extends Action
{
    protected Subject $subject;

    public function subject(Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public static function getDefaultName(): ?string
    {
        return 'createLearningMaterial';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Materi Baru');
        $this->modalHeading('Unggah materi baru');
        $this->modalSubmitActionLabel('Unggah');
        $this->successNotificationTitle('Materi berhasil diunggah');
        $this->closeModalByClickingAway(false);

        $this->form(function (Form $form) {
            /** @var \Illuminate\Support\Collection $meetings */
            $meetings = once(fn() => $this->subject
                ->schedules()
                ->whereBetween('end_time', [now()->subWeek(), now()->addMonth()])
                ->pluck('start_time', 'meeting_no'));

            return $form->columns(1)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),

                DateRangePicker::make('published_at')
                    ->label('Terbitkan pada')
                    ->placeholder('Langsung')
                    ->timePicker()
                    ->timePicker24()
                    ->timePickerIncrement(30)
                    ->singleCalendar()
                    ->drops(DropDirection::DOWN)
                    ->displayFormat('DD/MM/YYYY \P\u\k\u\l HH:mm')
                    ->startDate($meetings->first())
                    ->endDate($meetings->last())
                    ->default(null)
                    ->useRangeLabels()
                    ->alwaysShowCalendar()
                    ->ranges(
                        $meetings->mapWithKeys(fn(Carbon $startTime, int $meetingNo) => [
                            "Pertemuan $meetingNo" => [$startTime, $startTime],
                        ])->all()
                    ),

                Forms\Components\RichEditor::make('content')
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
            DB::transaction(function () use ($data, $form, $livewire) {
                $data['type'] = PostType::LearningMaterial;

                $record = $this->subject->posts()->create($data);

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
        });
    }
}

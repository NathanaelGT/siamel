<?php

namespace App\Filament\Student\Resources\SubjectResource\Pages;

use App\Enums\PostType;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\Attachment;
use App\Models\Post;
use App\Models\Subject;
use App\Models\Submission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

/** @property-read Submission $record */
class UploadSubmission extends EditRecord
{
    use WithFileUploads;

    #[Locked]
    public Subject | string $subject;

    #[Locked]
    public Post | string $post;

    protected static string $resource = SubjectResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('note')
                ->label('Catatan'),

            Forms\Components\FileUpload::make('attachments')
                ->label('Berkas')
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->openable()
                ->disk('local')
                ->directory(fn() => 'subject/' . $this->subject->slug . '/submissions')
                ->visibility('private')
                ->storeFileNamesIn('attachment_file_names'),
        ]);
    }

    /** @param  Submission  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($record, $data) {
            $wasRecentlyUpdated = false;

            if ($record->exists) {
                if ($record->note != $data['note']) {
                    $wasRecentlyUpdated = $record->update($data);
                }
            } else {
                // id assignment sama dengan id post
                $data['assignment_id'] = $this->post->id;

                $record->fill($data)->save();
                $wasRecentlyUpdated = true;
            }

            $attachments = $record->attachments->mapWithKeys(fn(Attachment $attachment) => [
                $attachment->slug => $attachment->name,
            ]);

            $newAttachments = collect($data['attachment_file_names'])->diffAssoc($attachments);
            $removedAttachments = $attachments->diffAssoc($data['attachment_file_names']);

            $shouldTouch = false;
            if ($newAttachments->isNotEmpty()) {
                $ownerId = auth()->id();

                $record->attachments()->createMany(
                    $newAttachments->map(fn(string $originalName, string $storedPath) => [
                        'owner_id' => $ownerId,
                        'name'     => $originalName,
                        'path'     => $storedPath,
                        'slug'     => $storedPath,
                    ])
                );

                $shouldTouch = true;
            }
            if ($removedAttachments->isNotEmpty()) {
                $shouldTouch = (bool) $record->attachments()
                    ->whereIn('id', $record->attachments
                        ->filter(fn(Attachment $attachment) => $removedAttachments->has($attachment->slug))
                        ->pluck('id'))
                    ->delete();
            }

            if ($shouldTouch && ! $wasRecentlyUpdated) {
                $record->touch();
            }
        });

        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        return SubjectResource::getUrl('post', [$this->subject, $this->post]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengumpulan berhasil disimpan';
    }

    public function mount(int | string | null $record = null): void
    {
        parent::mount($record ?? '');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $attachments = $this->record->attachments->pluck('name', 'path');

        $data['attachment_file_names'] = $attachments->all();
        $data['attachments'] = $attachments->keys()->all();

        return $data;
    }

    public function getTitle(): string
    {
        $prefix = $this->post->type->value;

        $title = str($this->post->title)->lower()->startsWith(strtolower($prefix))
            ? Str::ucfirst($this->post->title)
            : $this->post->type->value . ' ' . $this->post->title;

        if ($this->post->type === PostType::Assignment) {
            return "$title ({$this->post->assignment->type->value})";
        }

        return $title;
    }

    protected function resolveRecord(string | int | null $key): Submission
    {
        $this->subject = Subject::query()
            ->where('slug', $this->subject)
            ->firstOrFail();

        $this->post = $this->subject
            ->posts()
            ->findOrFail($this->post);

        return $this->post
            ->assignment
            ->submissions()
            ->whereStudent(auth()->user()->info_id, $this->post->subject_id)
            ->firstOrNew();
    }

    public function getBreadcrumb(): string
    {
        return implode(' - ', [
            $this->subject->course->name . ' ' . $this->subject->parallel . $this->subject->code,
            $this->subject->semester->academic_year,
        ]);
    }

    public function getModel(): string
    {
        return Post::class;
    }

    protected function authorizeAccess(): void
    {
        //
    }
}

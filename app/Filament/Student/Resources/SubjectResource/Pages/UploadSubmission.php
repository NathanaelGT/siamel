<?php

namespace App\Filament\Student\Resources\SubjectResource\Pages;

use App\Filament\Student\Resources\SubjectResource;
use App\Models\Attachment;
use App\Models\Post;
use App\Models\Subject;
use App\Models\Submission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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

    protected ?bool $hasDatabaseTransactions = false;

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
        $result = $this->wrapInDatabaseTransaction(function () use ($record, $data) {
            $wasRecentlyUpdated = false;

            if ($record->exists) {
                if ($record->note != $data['note']) {
                    $wasRecentlyUpdated = $record->update($data);
                }
            } else {
                // id assignment sama dengan id post
                $data['assignment_id'] = $this->post->id;
                $data['note'] ??= '';

                try {
                    $record->fill($data)->save();
                } catch (QueryException $exception) {
                    if (str($exception->getMessage())->contains("Column 'submissionable_id' cannot be null")) {
                        return null;
                    }

                    throw $exception;
                }
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

            return $record;
        });

        if ($result !== null) {
            return $result;
        }

        Notification::make()
            ->danger()
            ->title('Anda belum bergabung dalam kelompok')
            ->body('Silakan bergabung dalam kelompok terlebih dahulu')
            ->persistent()
            ->actions([
                Action::make('search-for-group')
                    ->label('Cari Kelompok')
                    ->url(SubjectResource::getUrl('view', [$this->subject]) . '?activeRelationManager=1'),
            ])
            ->send();

        $this->halt();
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
        return 'Pengumpulan';
    }

    public function getBreadcrumbs(): array
    {
        $url = fn(string $name = 'index', array $parameters = []) => SubjectResource::getUrl($name, $parameters);

        $breadcrumbs = [];
        $breadcrumbs[$url()] = SubjectResource::getBreadcrumb();
        $breadcrumbs[$viewUrl = $url('view', [$this->subject])] = $this->subject->title;
        $breadcrumbs["$viewUrl?activeRelationManager=0"] = SubjectResource\RelationManagers\PostsRelationManager::getTitle(
            $this->subject,
            SubjectResource::getPages()['view']->getPage()
        );
        $breadcrumbs[$url('post', [$this->subject, $this->post])] = $this->post->formatted_title;
        $breadcrumbs[] = $this->getTitle();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    protected function resolveRecord(string | int | null $key): Submission
    {
        $this->subject = SubjectResource::getEloquentQuery()
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

    public function getModel(): string
    {
        return Post::class;
    }

    protected function authorizeAccess(): void
    {
        //
    }
}

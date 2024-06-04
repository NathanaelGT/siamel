<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Filament\Professor\Resources\SubjectResource;
use App\Infolists\Components\AttachmentListEntry;
use App\Models\Post;
use App\Models\Subject;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Locked;

/**
 * @property-read Submission $record
 */
class SubmissionDetail extends ViewRecord
{
    #[Locked]
    public int | string $submissionId;

    #[Locked]
    public int | string $postId;

    #[Locked]
    public Post $post;

    #[Locked]
    public Subject $subject;

    protected static string $resource = SubjectResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()->columns(2)->schema([
                    Infolists\Components\TextEntry::make('submissionable_id')
                        ->label('NPM'),

                    Infolists\Components\TextEntry::make('submissionable.name')
                        ->label('Nama'),

                    Infolists\Components\TextEntry::make('submitted_at')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('updated_at')
                        ->formatStateUsing(function (Submission $record) {
                            // habis ngeupdate nilai, entah kenapa updated_at nya engga kecast
                            $updatedAt = Carbon::parse($record->updated_at);
                            if ($updatedAt->is($record->submitted_at)) {
                                return new HtmlString('
                                    <div class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500">
                                        Tidak ada perubahan
                                    </div>
                                ');
                            }

                            return $updatedAt->format(Infolist::$defaultDateTimeDisplayFormat);
                        }),

                    Infolists\Components\TextEntry::make('scored_at')
                        ->placeholder('Belum dinilai')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('score')
                        ->placeholder('Belum dinilai'),
                ]),

                Infolists\Components\Section::make()->columns(1)->schema([
                    Infolists\Components\TextEntry::make('note')
                        ->placeholder('Tidak ada'),

                    AttachmentListEntry::make('attachments')
                        ->label('Berkas'),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('score')
                ->label('Nilai')
                ->successRedirectUrl(fn() => SubjectResource::getUrl('post', [
                    $this->subject,
                    $this->post,
                ]))
                ->successNotificationTitle(fn() => "Tugas {$this->record->submitter_title} berhasil dinilai")
                ->form(fn() => [
                    Forms\Components\TextInput::make('score')
                        ->hiddenLabel()
                        ->integer()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                ])
                ->fillForm(['score' => $this->record->score])
                ->action(function (Action $action, array $data) {
                    $this->record->timestamps = false;
                    $this->record->update([
                        'score'     => $data['score'],
                        'scored_at' => $this->record->freshTimestamp(),
                    ]);

                    $action->success();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Tugas ' . $this->record->submitter_title;
    }

    public function getBreadcrumbs(): array
    {
        $url = fn(string $name = 'index', array $parameters = []) => SubjectResource::getUrl($name, $parameters);

        $breadcrumbs = [];
        $breadcrumbs[$url()] = SubjectResource::getBreadcrumb();
        $breadcrumbs[$viewUrl = $url('view', [$this->subject])] = implode(' - ', [
            $this->subject->title,
            $this->subject->semester->academic_year,
        ]);
        $breadcrumbs["$viewUrl?activeRelationManager=0"] = SubjectResource\RelationManagers\PostsRelationManager::getTitle(
            $this->subject,
            SubjectResource::getPages()['view']->getPage()
        );
        $breadcrumbs[$url('post', [$this->subject, $this->post])] = $this->post->formatted_title;
        $breadcrumbs[] = $this->record->submitter_title;

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getModel(): string
    {
        return Post::class;
    }

    protected function resolveRecord(int | string $key): Submission
    {
        $this->subject = Subject::query()->where('slug', $key)->firstOrFail();
        $this->post = $this->subject->posts()->findOrFail($this->postId);

        return $this->post->submissions()->findOrFail($this->submissionId);
    }
}

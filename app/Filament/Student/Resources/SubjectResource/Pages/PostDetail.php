<?php

namespace App\Filament\Student\Resources\SubjectResource\Pages;

use App\Enums\PostType;
use App\Filament\Student\Resources\SubjectResource;
use App\Infolists\Components\AttachmentListEntry;
use App\Models\Post;
use App\Models\Subject;
use App\Providers\FilamentServiceProvider;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Thiktak\FilamentSimpleListEntry\Infolists\Components\SimpleListEntry;

/**
 * @property-read Post $record
 * @property-read Subject $subject
 */
class PostDetail extends ViewRecord
{
    #[Locked]
    public int | string $postId;

    #[Locked]
    public Subject $subject;

    protected static string $resource = SubjectResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $isAssignment = $this->record->assignment !== null;

        /** @var ?\App\Models\Submission $submission */
        $submission = $this->record
            ->assignment
            ?->submissions()
            ->whereStudent(auth()->user()->student, $this->record->subject_id)
            ->first(['id', 'scored_at', 'updated_at']);

        return $infolist->columns(2)->schema(array_filter([
            Infolists\Components\Section::make()->columnSpan(1)->columns(2)->schema(array_filter([
                Infolists\Components\TextEntry::make('type')
                    ->formatStateUsing(function (Post $record) {
                        if ($record->type === PostType::Assignment) {
                            return $record->type->value . ' ' . $record->assignment->type->value;
                        }

                        return $record->type->value;
                    }),

                Infolists\Components\TextEntry::make('assignment.category'),

                $this->record->attachments->isNotEmpty()
                    ? AttachmentListEntry::make('attachments')
                    ->columnSpan(2)
                    : null,
            ])),

            ! $isAssignment ? null : SimpleListEntry::make('')
                ->getStateUsing([
                    [
                        'key'   => 'Status pengajuan',
                        'value' => match (true) {
                            $submission === null            => 'Belum ada',
                            $submission->scored_at === null => 'Sudah dikumpulkan',
                            default                         => 'Sudah dinilai',
                        },
                    ],
                    [
                        'key'   => 'Batas tanggal terima',
                        'value' => $this->record
                            ->assignment
                            ->deadline
                            ->translatedFormat(FilamentServiceProvider::DEFAULT_DATE_TIME_DISPLAY_FORMAT),
                    ],
                    $submission === null ? [
                        'key'   => 'Waktu yang tersisa',
                        'value' => $this->record->assignment->deadline->diffForHumans(parts: 7),
                    ] : [
                        'key'   => 'Terakhir diubah',
                        'value' => function () use ($submission) {
                            if ($submission === null) {
                                return '-';
                            }

                            $lastUpdated = $this->record->assignment->deadline->isPast()
                                ? $submission->updated_at->translatedFormat(FilamentServiceProvider::DEFAULT_DATE_TIME_DISPLAY_FORMAT)
                                : $submission->updated_at->diffForHumans(parts: 7);

                            if ($submission->updated_at->gt($this->record->assignment->deadline)) {
                                $due = $this->record->assignment->deadline->diffForHumans(parts: 7);
                                $rgb = Color::Red[600];

                                return new HtmlString(
                                    "<span title=\"Batas akhir pengumpulan: $due\" style=\"color:rgb($rgb)\">" .
                                    $lastUpdated .
                                    '</span>'
                                );
                            }

                            return $lastUpdated;
                        },
                    ],
                    [
                        'key'     => 'Berkas',
                        'value'   => function () use ($submission) {
                            if ($submission === null) {
                                return '-';
                            }

                            return new HtmlString(Blade::render(
                                '<x-attachments size="small" :$attachments />',
                                ['attachments' => $submission->attachments]
                            ));
                        },
                        'actions' => [
                            Infolists\Components\Actions\Action::make('upload')
                                ->url(SubjectResource::getUrl('upload', [$this->subject, $this->record])),
                        ],
                    ],
                ])
                ->itemLabel(fn(array $record) => $record['key'])
                ->itemDescription(fn(array $record) => value($record['value']))
                ->itemActions(fn(array $record) => $record['actions'] ?? null),

            filled($this->record->content)
                ? Infolists\Components\Section::make()->columnSpan(2)->columns(1)->schema([
                Infolists\Components\TextEntry::make('content')
                    ->html(),
            ])
                : null,
        ]));
    }

    public function getTitle(): string
    {
        $prefix = $this->record->type->value;

        $title = str($this->record->title)->lower()->startsWith(strtolower($prefix))
            ? Str::ucfirst($this->record->title)
            : $this->record->type->value . ' ' . $this->record->title;

        if ($this->record->type === PostType::Assignment) {
            return "$title ({$this->record->assignment->type->value})";
        }

        return $title;
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

    protected function resolveRecord(int | string $key): Post
    {
        $this->subject = Subject::query()->where('slug', $key)->first();
        if ($this->subject === null) {
            throw (new ModelNotFoundException)->setModel(Subject::class, [$key]);
        }

        $post = $this->subject->posts()->find($this->postId);
        if ($post === null) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$this->postId]);
        }

        return $post;
    }
}

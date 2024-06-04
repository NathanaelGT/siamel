<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Enums\PostType;
use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\Professor\Resources\SubjectResource\RelationManagers;
use App\Infolists\Components\AttachmentListEntry;
use App\Models\Post;
use App\Models\Subject;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Locked;

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
        return $infolist
            ->schema(array_filter([
                Infolists\Components\Section::make()->schema(array_filter([
                    Infolists\Components\Grid::make()
                        ->schema($dateSchema = array_filter([
                            Infolists\Components\TextEntry::make('published_at')
                                ->dateTime(),

                            $this->record->created_at->equalTo($this->record->updated_at)
                                ? null
                                : Infolists\Components\TextEntry::make('updated_at')
                                ->dateTime(),

                            $this->record->type !== PostType::Assignment
                                ? Infolists\Components\TextEntry::make('type')
                                : Infolists\Components\TextEntry::make('assignment.deadline')
                                ->dateTime(),
                        ]))
                        ->columns(count($dateSchema)),

                    $this->record->type !== PostType::Assignment ? null :
                        Infolists\Components\Grid::make()->schema([
                            Infolists\Components\TextEntry::make('assignment.category'),
                        ]),
                ])),

                filled($this->record->content) || $this->record->attachments->isNotEmpty()
                    ? Infolists\Components\Section::make()->schema(array_filter([
                    filled($this->record->content) ? Infolists\Components\TextEntry::make('content')
                        ->hiddenLabel()
                        ->html()
                        ->columnSpan(2) : null,

                    $this->record->attachments->isNotEmpty() ? AttachmentListEntry::make('attachments')
                        ->hiddenLabel()
                        ->columnSpan(2) : null,
                ])) : null,
            ]));
    }

    public function getRelationManagers(): array
    {
        if ($this->record->type === PostType::Assignment) {
            return [
                RelationManagers\SubmissionsRelationManager::class,
            ];
        }

        return [];
    }

    public function getTitle(): string
    {
        return $this->record->formatted_title;
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
        $breadcrumbs[] = $this->getTitle();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getModel(): string
    {
        return Post::class;
    }

    protected function resolveRecord(int | string $key): Post
    {
        $this->subject = Subject::query()->where('slug', $key)->firstOrFail();

        return $this->subject->posts()->findOrFail($this->postId);
    }
}

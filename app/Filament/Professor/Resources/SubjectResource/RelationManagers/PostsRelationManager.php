<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Enums\PostType;
use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Models\Post;
use App\Models\SubjectSchedule;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/** @property-read \App\Models\Subject $ownerRecord */
class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Perkuliahan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->paginated(false)
            ->defaultGroup(
                Tables\Grouping\Group::make('meeting_no')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(function (SubjectSchedule $record) {
                        $date = $record->start_time->translatedFormat('l, j F Y \P\u\k\u\l H:i');

                        return "Pertemuan ke $record->meeting_no ($date)";
                    })
            )
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('title')
                        ->placeholder(fn(SubjectSchedule $record) => $record->start_time->isFuture()
                            ? 'Belum ada aktivitas pada pertemuan ini'
                            : 'Tidak ada aktivitas pada pertemuan ini'),
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hidden(fn(SubjectSchedule $record) => $record->title === null)
                    ->url(function (SubjectSchedule $record) {
                        return SubjectResource::getUrl('post', [$this->ownerRecord, $record->post_id]);
                    })
                    ->badge(function (SubjectSchedule $record) {
                        return $record->type === PostType::Assignment->value
                            ? ($record->count ?: '-')
                            : null;
                    }),
            ])
            ->headerActions([
                SubjectResource\Actions\CreateLearningMaterialAction::make()
                    ->subject($this->ownerRecord)
                    ->authorize(fn() => $this->can('create', new Post)),

                SubjectResource\Actions\CreateAssignmentAction::make()
                    ->subject($this->ownerRecord)
                    ->authorize(fn() => $this->can('create', new Post)),
            ]);
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()->query(function () {
            $assignment = PostType::Assignment->value;

            return SubjectSchedule::query()
                ->select([
                    'subject_schedules.id',
                    'subject_schedules.start_time',
                    'subject_schedules.meeting_no',
                    'posts.id as post_id',
                    'posts.title',
                    'posts.type',
                    DB::raw("if(`posts`.`type` = '$assignment', (select count(*) from `submissions` where `submissions`.`assignment_id` = `posts`.`id`), 0) as `count`"),
                ])
                ->leftJoin('posts', function (JoinClause $join) {
                    $join->on('posts.subject_id', '=', 'subject_schedules.subject_id')
                        ->whereRaw('week(`posts`.`published_at`) = week(`subject_schedules`.`start_time`)');
                })
                ->where('subject_schedules.subject_id', $this->ownerRecord->id)
                ->orderBy('subject_schedules.start_time')
                ->orderBy('posts.published_at');
        });
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        $action
            ->authorize(fn(self $livewire, Model $record) => $livewire->canView($record))
            ->infolist(fn(Infolist $infolist) => $this->infolist($infolist))
            ->form(fn(Form $form) => $this->form($form));
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->authorize(fn(self $livewire) => $livewire->canCreate())
            ->form(fn(Form $form) => $this->form($form));
    }
}

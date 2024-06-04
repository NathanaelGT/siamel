<?php

namespace App\Filament\Student\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\SubjectSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Query\JoinClause;

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
                        $date = $record->start_time->translatedFormat('l, j F Y');

                        return "Pertemuan ke $record->meeting_no ($date)";
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->placeholder(fn(SubjectSchedule $record) => $record->start_time->isFuture()
                        ? 'Belum ada aktivitas pada pertemuan ini'
                        : 'Tidak ada aktivitas pada pertemuan ini'
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hidden(fn(SubjectSchedule $record) => $record->title === null)
                    ->url(function (SubjectSchedule $record) {
                        return SubjectResource::getUrl('post', [$this->ownerRecord, $record->post_id]);
                    }),
            ]);
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()->query(function () {
            return SubjectSchedule::query()
                ->select([
                    'subject_schedules.id',
                    'subject_schedules.start_time',
                    'subject_schedules.meeting_no',
                    'posts.id as post_id',
                    'posts.title',
                ])
                ->leftJoin('posts', function (JoinClause $join) {
                    $join->on('posts.subject_id', '=', 'subject_schedules.subject_id')
                        ->whereRaw('WEEK(posts.published_at) = WEEK(subject_schedules.start_time)');
                })
                ->where('subject_schedules.subject_id', $this->ownerRecord->id)
                ->orderBy('subject_schedules.start_time')
                ->orderBy('posts.published_at');
        });
    }
}

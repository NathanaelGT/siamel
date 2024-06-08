<?php

namespace App\Filament\Student\Resources\SubjectResource\RelationManagers;

use App\Enums\PostType;
use App\Filament\RelationManager;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\SubjectSchedule;
use App\Models\Submission;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

/** @property-read \App\Models\Subject $ownerRecord */
class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Perkuliahan';

    public function table(Table $table): Table
    {
        $submissionExistMap = function () {
            static $result = Submission::query()
                ->whereStudent(auth()->user()->student, $this->ownerRecord)
                ->whereIn('assignment_id', $this->getTableRecords()->pluck('post_id')->filter())
                ->pluck('submissions.assignment_id')
                ->mapWithKeys(fn(int $assignmentId) => [$assignmentId => $assignmentId]);

            return $result;
        };

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
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('title')
                        ->formatStateUsing(function (SubjectSchedule $record) {
                            $title = str($record->title)->lower()->startsWith(strtolower($record->post_type))
                                ? Str::ucfirst($record->title)
                                : $record->post_type . ' ' . $record->title;

                            if ($record->post_type === PostType::Assignment->value) {
                                return "$title ({$record->assignment_type})";
                            }

                            return $title;
                        })
                        ->placeholder(fn(SubjectSchedule $record) => $record->start_time->isFuture()
                            ? 'Belum ada aktivitas pada pertemuan ini'
                            : 'Tidak ada aktivitas pada pertemuan ini'
                        ),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('upload')
                    ->visible(fn(SubjectSchedule $record) => $record->post_type === PostType::Assignment->value)
                    ->icon('heroicon-m-cloud-arrow-up')
                    ->tooltip(fn(SubjectSchedule $record) => $submissionExistMap()[$record->post_id] ?? false
                        ? null
                        : 'Anda belum mengumpulkan tugas ini')
                    ->color(fn(SubjectSchedule $record) => $submissionExistMap()[$record->post_id] ?? false
                        ? 'success'
                        : 'warning')
                    ->url(function (SubjectSchedule $record) {
                        return SubjectResource::getUrl('upload', [$this->ownerRecord, $record->post_id]);
                    }),

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
                    'posts.type as post_type',
                    'assignments.type as assignment_type',
                ])
                ->leftJoin('posts', function (JoinClause $query) {
                    $query->on('posts.subject_id', '=', 'subject_schedules.subject_id')
                        ->whereRaw('WEEK(posts.published_at) = WEEK(subject_schedules.start_time)');
                })
                ->leftJoin('assignments', function (JoinClause $query) {
                    $query->on('assignments.id', '=', 'posts.id');
                })
                ->where('subject_schedules.subject_id', $this->ownerRecord->id)
                ->orderBy('subject_schedules.start_time')
                ->orderBy('posts.published_at');
        });
    }
}

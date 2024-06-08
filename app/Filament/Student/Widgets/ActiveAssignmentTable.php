<?php

namespace App\Filament\Student\Widgets;

use App\Enums\PostType;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\Post;
use App\Models\Semester;
use App\Models\Subject;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ActiveAssignmentTable extends BaseWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Tugas Yang Belum Dikumpulkan')
            ->emptyStateHeading('Semua tugas sudah dikumpulkan')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('subject.course.name')
                        ->formatStateUsing(fn(string $state) => new HtmlString('<strong>' . e($state) . '</strong>'))
                        ->url(fn(Post $record) => SubjectResource::getUrl('view', [$record->subject])),

                    Tables\Columns\TextColumn::make('title')
                        ->formatStateUsing(function (Post $record) {
                            $title = str($record->title)->lower()->startsWith('tugas')
                                ? Str::ucfirst($record->title)
                                : "Tugas $record->title";

                            return "$title ($record->assignment_type)";
                        })
                        ->url(fn(Post $record) => SubjectResource::getUrl('post', [$record->subject, $record])),

                    Tables\Columns\TextColumn::make('deadline')
                        ->prefix('Tenggat: ')
                        ->tooltip(function (Post $record) {
                            $record->deadline = Carbon::parse($record->deadline);

                            return $record->deadline->diffForHumans(parts: 7);
                        })
                        ->formatStateUsing(function (Post $record) {
                            $deadlineText = $record->deadline->translatedFormat('l, j F Y \P\u\k\u\l H:i');

                            $color = match (true) {
                                $record->deadline->isPast()               => 'danger',
                                abs($record->deadline->diffInDays()) <= 1 => 'warning',
                                default                                   => null,
                            };

                            if ($color === null) {
                                return $deadlineText;
                            }

                            $html = "<span class=\"fi-ta-text-item-label text-sm leading-6 text-custom-600 dark:text-custom-400\" style=\"--c-400:var(--$color-400);--c-600:var(--$color-600);\">";
                            $html .= $deadlineText;
                            $html .= '</span>';

                            return new HtmlString($html);
                        }),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('upload')
                    ->icon('heroicon-m-cloud-arrow-up')
                    ->url(fn(Post $record) => SubjectResource::getUrl('upload', [$record->subject, $record])),
            ])
            ->query(function () {
                return Post::query()
                    ->select([
                        'posts.id',
                        'subject_id',
                        'title',
                        'assignments.type as assignment_type',
                        'deadline',
                    ])
                    ->join('assignments', 'posts.id', '=', 'assignments.id')
                    ->with(['subject.course'])
                    ->whereIn(DB::raw('(`posts`.`id`, false)'), Post::query()
                        ->select(['id'])
                        ->whereIn('subject_id', Subject::query()
                            ->where('semester_id', Semester::current()->id)
                            ->whereStudent(auth()->user()->info_id)
                            ->select('id'))
                        ->where('type', PostType::Assignment)
                        ->withExists(['submissions']))
                    ->orderBy('deadline');
            });
    }
}

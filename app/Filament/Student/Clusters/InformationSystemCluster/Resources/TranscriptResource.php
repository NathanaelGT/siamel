<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources;

use App\Enums\Parity;
use App\Filament\Resource;
use App\Filament\Student\Clusters\InformationSystemCluster;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Pages;
use App\Models\Semester;
use App\Models\Subject;
use App\Service\Subject\Score;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class TranscriptResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = InformationSystemCluster::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $breadcrumb = 'Transkrip';

    protected static ?string $modelLabel = 'Transkrip';

    protected static ?string $slug = 'transkrip';

    public static int $highestSemester = 0;

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->default(fn(stdClass $rowLoop) => $rowLoop->iteration),

                Tables\Columns\TextColumn::make('course.name'),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester ke')
                    ->formatStateUsing(function (Subject $record) {
                        static $enrolledYear = auth()->user()->student->enrolled_year;

                        $semester = intval(($record->semester->year - $enrolledYear) * 2);
                        if ($record->semester->parity === Parity::Odd) {
                            $semester++;
                        }

                        if ($semester > static::$highestSemester) {
                            static::$highestSemester = $semester;
                        }

                        return $semester;
                    }),

                Tables\Columns\TextColumn::make('course.credits')
                    ->summarize([
                        TranscriptResource\Summarizers\CreditSummarizer::make(),
                    ]),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->default(function (Subject $record) {
                        $record->credit = Score::scoreToWeight(
                            round($record->submissions->avg('score'))
                        );

                        return Score::weightToLetter($record->credit);
                    }),

                Tables\Columns\TextColumn::make('credit')
                    ->formatStateUsing(fn(int $state) => $state / 100)
                    ->label('Kredit'),

                Tables\Columns\TextColumn::make('nxk')
                    ->label('NxK')
                    ->default(fn(Subject $record) => $record->nxk = $record->credit * $record->course->credits / 100)
                    ->summarize([
                        TranscriptResource\Summarizers\NxKSummarizer::make(),
                    ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->with([
                        'course',
                        'semester',
                        'submissions' => function (HasManyThrough $query) {
                            $query->whereStudent(auth()->user()->info_id);
                        },
                    ])
                    ->join('student_subject', function (JoinClause $join) {
                        $join->on('subjects.id', '=', 'student_subject.subject_id')
                            ->where('student_subject.student_id', auth()->user()->info_id);
                    })
                    ->where('semester_id', '<', Semester::current()->id)
                    ->orderBy('semester_id');
            });
    }

    public static function getWidgets(): array
    {
        return [
            TranscriptResource\Widgets\GPAOverview::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranscipts::route('/'),
        ];
    }
}

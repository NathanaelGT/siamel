<?php

namespace App\Filament\Staff\Resources\StudentResource\RelationManagers;

use App\Enums\Parity;
use App\Filament\RelationManager;
use App\Filament\Tables\Summarizer\LocalSum;
use App\Models\Semester;
use App\Models\Subject;
use App\Service\Subject\Score;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\HtmlString;

/** @property-read \App\Models\Student $ownerRecord */
class TranscriptsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    protected static ?string $title = 'Transkrip';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('course.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course.name')
            ->description(function () use ($table) {
                $credits = 0;
                $nxk = 0;
                $highestSemester = 0;

                $table->getRecords()->each(function (Subject $record) use (&$credits, &$nxk, &$highestSemester) {
                    $credits += $record->course->credits;

                    $record->score = Score::scoreToWeight(
                        round($record->submissions->avg('score'))
                    );

                    $nxk += $record->nxk = $record->score * $record->course->credits / 100;

                    static $enrolledYear = $this->ownerRecord->enrolled_year;

                    $record->semesterNo = intval(($record->semester->year - $enrolledYear) * 2);
                    if ($record->semester->parity === Parity::Odd) {
                        $record->semesterNo++;
                    }

                    if ($record->semesterNo > $highestSemester) {
                        $highestSemester = $record->semesterNo;
                    }
                });

                $gpa = number_format($nxk / $credits, 2);

                return new HtmlString("Indeks Prestasi: $gpa<br>SKS Kumulatif: $credits<br>Semester: $highestSemester");
            })
            ->paginated(false)
            ->defaultGroup(Tables\Grouping\Group::make('semester_id')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(fn(Subject $record) => "Semester $record->semesterNo"))
            ->columns([
                Tables\Columns\TextColumn::make('course.name'),

                Tables\Columns\TextColumn::make('course.credits')
                    ->summarize([
                        $courseCreditSum = LocalSum::make(),
                    ])
                    ->formatStateUsing($courseCreditSum->increaseValue(...)),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->formatStateUsing(fn(int $state) => Score::weightToLetter($state)),

                Tables\Columns\TextColumn::make('credit')
                    ->label('Kredit')
                    ->default(fn(Subject $record) => $record->score / 100),

                Tables\Columns\TextColumn::make('nxk')
                    ->label('NxK')
                    ->summarize([
                        $nxkSum = LocalSum::make(),
                    ])
                    ->formatStateUsing($nxkSum->increaseValue(...)),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->with([
                        'course',
                        'semester',
                        'submissions' => function (HasManyThrough $query) {
                            $query->whereStudent($this->ownerRecord->id);
                        },
                    ])
                    ->where('semester_id', '<', Semester::current()->id)
                    ->orderBy('semester_id');
            });
    }
}

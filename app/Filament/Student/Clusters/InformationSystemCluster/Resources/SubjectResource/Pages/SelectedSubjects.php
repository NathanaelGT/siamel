<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Pages;

use App\Enums\WorkingDay;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource;
use App\Models\Semester;
use App\Models\StudentSubject;
use App\Models\Subject;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class SelectedSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->numeric()
                    ->default(fn(stdClass $rowLoop) => $rowLoop->iteration),

                Tables\Columns\TextColumn::make('title'),

                Tables\Columns\TextColumn::make('course.credits')
                    ->summarize([
                        SubjectResource\Summarizers\CreditSummarizer::make(),
                    ]),

                Tables\Columns\TextColumn::make('day'),

                Tables\Columns\TextColumn::make('time'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(fn(Subject $record) => 'Hapus ' . $record->title)
                    ->successNotificationTitle(function (Subject $record) {
                        return "Kelas {$record->title} berhasil dihapus";
                    })
                    ->using(function (Subject $record) {
                        StudentSubject::query()
                            ->where('student_id', auth()->user()->info_id)
                            ->where('subject_id', $record->id)
                            ->delete();
                    }),
            ]);
    }

    protected function getTableQuery(): ?Builder
    {
        return Subject::query()
            ->select('subjects.*')
            ->join('student_subject', function (JoinClause $join) {
                $join->on('subjects.id', '=', 'student_subject.subject_id')
                    ->where('student_subject.student_id', auth()->user()->info_id);
            })
            ->where('subjects.semester_id', Semester::current()->id)
            ->orderByRaw("field(`subjects`.`day`, ?, ?, ?, ?, ?) asc", WorkingDay::cases())
            ->orderBy('subjects.start_time');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah KRS'),
        ];
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}

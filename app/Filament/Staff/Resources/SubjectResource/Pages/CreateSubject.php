<?php

namespace App\Filament\Staff\Resources\SubjectResource\Pages;

use App\Enums\WorkingDay;
use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Building;
use App\Models\Course;
use App\Models\CourseProfessor;
use App\Models\Professor;
use App\Models\Room;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\Subject;
use App\Service\Subject\Slug;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;

    public function form(Form $form): Form
    {
        return $form->columns(1)->schema(function () {
            /** @var Forms\Components\Repeater $repeater */
            $repeater = null;

            $index = 0;

            $createFinder = static function (Builder | Closure $query, array $columns = []) {
                return static function ($id = null) use ($query, $columns) {
                    static $cache = false;
                    if ($cache === false) {
                        if (filled($id)) {
                            if ($query instanceof Builder) {
                                $cache = $query->find($id, $columns);
                            } else {
                                $cache = $query($id);
                            }
                        } else {
                            $cache = null;
                        }
                    }

                    return $cache;
                };
            };

            $course = $createFinder(Course::query(), ['name', 'study_program_id']);
            $room = $createFinder(Room::query(), ['capacity']);

            $professors = $createFinder(static fn($courseId) => Professor::query()
                ->join('users', 'professors.user_id', '=', 'users.id')
                ->whereIn(
                    'professors.id',
                    CourseProfessor::query()
                        ->where('course_id', $courseId)
                        ->select('professor_id')
                )
                ->get(['professors.id', 'users.name'])
            );

            $rooms = $createFinder(static fn($studyProgramId) => Room::query()
                ->whereIn(
                    'building_id',
                    Building::query()
                        ->select('id')
                        ->where(
                            'faculty_id',
                            StudyProgram::query()
                                ->select('faculty_id')
                                ->where('id', $studyProgramId),
                        )
                )
                ->get(['id', 'name', 'capacity'])
            );

            $newParallel = [];

            return [
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Mata Kuliah')
                        ->columns(2)
                        ->afterValidation(static function (Forms\Get $get) use (&$repeater, &$newParallel) {
                            $parallel = $get('parallel');

                            $start = count($parallel);
                            $end = $get('parallel_count');

                            if ($start > $end) {
                                for ($i = $start; $i > $end; $i--) {
                                    array_pop($parallel);
                                }
                            } else {
                                for ($i = $start; $i < $end; $i++) {
                                    $uuid = $repeater->generateUuid();
                                    $parallel[$uuid] = [];
                                    $newParallel[] = $uuid;
                                }
                            }
                        })
                        ->schema([
                            Forms\Components\Select::make('course_id')
                                ->relationship('course', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('semester_id')
                                ->relationship('semester', 'academic_year', function (Builder $query) {
                                    $query
                                        ->limit(2)
                                        ->orderBy('year', 'desc')
                                        ->orderBy('parity');
                                })
                                ->preload()
                                ->selectablePlaceholder(false)
                                ->required(),
                        ]),

                    Forms\Components\Wizard\Step::make('Paralel')->schema(function () use (
                        $course, $professors, $rooms, $room, &$index, &$repeater
                    ) {
                        $chr = ord(
                            Subject::query()
                                ->where('course_id', $this->data['course_id'])
                                ->where('semester_id', $this->data['semester_id'])
                                ->orderBy('id', 'desc')
                                ->value('parallel') ?? '@'
                        ); // @: 64, A: 65

                        return [
                            $repeater = Forms\Components\Repeater::make('parallel')
                                ->minItems(1)
                                ->maxItems(26)
                                ->cloneable()
                                ->reorderableWithButtons()
                                ->columns(2)
                                ->itemLabel(static function (Forms\Get $get) use ($course, $chr, &$index) {
                                    $name = $course($get('course_id'))?->name;
                                    $parallel = chr($chr + ++$index);

                                    return new HtmlString("<span>$name {$parallel}081</span>");
                                })
                                ->schema(fn() => [
                                    Forms\Components\Select::make('professor_id')
                                        ->columnSpan(2)
                                        ->options(static function (Forms\Get $get) use ($professors) {
                                            return $professors($get('../../course_id'))?->pluck('name', 'id')->all();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Forms\Components\Select::make('room_id')
                                        ->options(static function (Forms\Get $get) use ($rooms, $course) {
                                            $studyProgramId = $course($get('../../course_id'))?->study_program_id;

                                            return $rooms($studyProgramId)?->pluck('name', 'id')->all();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(
                                            static function (Forms\Set $set, ?string $state) use ($room) {
                                                $set('capacity', $room($state)?->capacity);
                                            }
                                        ),

                                    Forms\Components\TextInput::make('capacity')
                                        ->readOnly(fn(Forms\Get $get) => $get('room_id') === null)
                                        ->placeholder(fn(Forms\Get $get) => $get('room_id') === null
                                            ? 'Harap pilih ruangan terlebih dahulu'
                                            : null)
                                        ->required()
                                        ->numeric()
                                        ->minValue(10)
                                        ->maxValue(static function (Forms\Get $get) use ($room) {
                                            return $room($get('room_id'))?->capacity ?? 50;
                                        }),

                                    Forms\Components\Select::make('day')
                                        ->options(WorkingDay::class)
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\Select::make('start_time')
                                        ->options([
                                            '07:00',
                                            '09:30',
                                            '13:00',
                                            '15:30',
                                        ])
                                        ->searchable()
                                        ->required(),
                                ]),
                        ];
                    }),
                ])
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getCreateFormAction()),
            ];
        });
    }

    protected function handleRecordCreation(array $data): Model
    {
        $semester = Semester::findOr($data['semester_id'], callback: $this->halt(...));
        $courseName = Course::findOr($data['course_id'], 'name', $this->halt(...))->name;
        $chr = ord(
            Subject::query()
                ->where('course_id', $this->data['course_id'])
                ->where('semester_id', $this->data['semester_id'])
                ->orderBy('id', 'desc')
                ->value('parallel') ?? '@'
        );

        $subject = null;

        $times = ['07:00', '09:30', '13:00', '15:30'];

        foreach ($data['parallel'] as $index => $parallel) {
            $parallel['semester_id'] = $semester->id;
            $parallel['course_id'] = $data['course_id'];
            $parallel['parallel'] = chr($chr + $index + 1);
            $parallel['code'] = '081';
            $parallel['slug'] = Slug::generate(
                $courseName,
                $semester->parity,
                $semester->year,
                $parallel['parallel'],
                $parallel['code']
            );
            $parallel['start_time'] = $times[$parallel['start_time']];
            $parallel['year'] = now()->format('Y');

            $s = Subject::create($parallel);
            $subject ??= $s;
        }

        return $subject;
    }

    protected function getRedirectUrl(): string
    {
        return SubjectResource::getUrl();
    }

    protected function getFormActions(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Staff\Resources\SubjectResource\Pages;

use App\Enums\WorkingDay;
use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Building;
use App\Models\CourseProfessor;
use App\Models\Professor;
use App\Models\Room;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EditSubject extends EditRecord
{
    protected static string $resource = SubjectResource::class;

    public function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('course.name')
                ->disabled(),

            Forms\Components\TextInput::make('semester.academic_year')
                ->disabled(),

            Forms\Components\Select::make('professor_id')
                ->columnSpan(2)
                ->relationship('professor', 'users.name', function (Builder $query) {
                    $query
                        ->join('users', 'professors.user_id', '=', 'users.id')
                        ->whereIn(
                            'professors.id',
                            CourseProfessor::query()
                                ->where('course_id', $this->record->course_id)
                                ->select('professor_id')
                        );
                })
                ->preload()
                ->searchable()
                ->required(),

            Forms\Components\Select::make('room_id')
                ->options(function () {
                    return Room::query()
                        ->whereIn(
                            'building_id',
                            Building::query()->select('id')->where(
                                'faculty_id',
                                Professor::query()->select('faculty_id')->where(
                                    'id',
                                    $this->record->professor_id
                                )
                            )
                        )
                        ->pluck('name', 'id')
                        ->all();
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                    if (blank($state)) {
                        return;
                    }

                    $set('capacity', Room::query()->where('id', $state)->value('capacity'));
                }),

            Forms\Components\TextInput::make('capacity')
                ->readOnly(fn(Forms\Get $get) => $get('room_id') === null)
                ->placeholder(fn(Forms\Get $get) => $get('room_id') === null
                    ? 'Harap pilih ruangan terlebih dahulu'
                    : null)
                ->required()
                ->numeric()
                ->minValue(10)
                ->maxValue(function (Forms\Get $get) {
                    if (blank($id = $get('room_id'))) {
                        return null;
                    }

                    static $cache = [];
                    if (! isset($cache[$id])) {
                        $cache[$id] = Room::where('id', $id)->value('capacity');
                    }

                    return $cache[$id];
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
                ->formatStateUsing(fn($state) => Carbon::parse($state)->format('H:i'))
                ->searchable()
                ->required(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function resolveRecord(int | string $key): Model
    {
        return parent::resolveRecord($key)
            ->load(['course:id,name', 'semester:id,academic_year']);
    }

    protected function fillForm(): void
    {
        $state = $this->getRecord()->toArray();

        $this->form->fill($state);
    }
}

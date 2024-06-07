<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Models\Student;
use App\Models\SubjectGroup;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** @property-read \App\Models\Subject $ownerRecord */
class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function table(Table $table): Table
    {
        static $groups = $this->ownerRecord->groups->load('members:id');

        return $table
            ->paginated(false)
            ->modelLabel('kelompok')
            ->recordTitleAttribute('name')
            ->emptyStateHeading('Tidak ada kelompok')
            ->emptyStateDescription(null)
            ->columns([
                Tables\Columns\TextColumn::make('name'),

                Tables\Columns\TextColumn::make('subject_group_members_count'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('create-bulk')
                    ->label('Buat')
                    ->createAnother(false)
                    ->hidden($groups->isNotEmpty())
                    ->successNotificationTitle('Kelompok berhasil dibuat')
                    ->form(fn(Form $form) => $form
                        ->model(SubjectGroup::class)
                        ->schema([
                            Forms\Components\TextInput::make('member_count')
                                ->label('Jumlah mahasiswa per kelompok')
                                ->required()
                                ->integer()
                                ->minValue(1)
                                ->maxValue(fn() => $this->ownerRecord->students()->count()),

                            Forms\Components\Select::make('strategy')
                                ->label('Anggota kelompok')
                                ->native(false)
                                ->default('empty')
                                ->options([
                                    'empty'  => 'Kosong',
                                    'random' => 'Acak',
                                    'id'     => 'Sesuai NPM',
                                ])
                                ->live()
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                    $set('student_can_manage_group', $get('strategy') === 'empty');
                                })
                                ->required(),

                            Forms\Components\Checkbox::make('student_can_manage_group')
                                ->label('Mahasiswa dapat mengelola kelompok')
                                ->default(true),
                        ]))
                    ->using(function (array $data, Tables\Actions\CreateAction $action) {
                        return DB::transaction(function () use ($data, $action) {
                            $studentIds = $this->ownerRecord->students()->pluck('id');
                            $groupCount = ceil($studentIds->count() / $data['member_count']);

                            $this->ownerRecord->update([
                                'student_can_manage_group' => $data['student_can_manage_group'],
                                'group_max_members'        => $data['member_count'],
                            ]);

                            $groups = $this->ownerRecord->groups()->createMany(
                                Collection::range(1, $groupCount)->map(fn($no) => [
                                    'name' => "Kelompok $no",
                                ])
                            );

                            if ($data['strategy'] !== 'empty') {
                                $groupMemberChunks = (match ($data['strategy']) {
                                    'random' => $studentIds->shuffle(),
                                    'id'     => $studentIds,
                                    default  => $action->halt(true),
                                })->chunk($data['member_count']);

                                $groups->each(function (SubjectGroup $group, int $index) use ($groupMemberChunks) {
                                    $group->members()->attach($groupMemberChunks[$index]);
                                });
                            }

                            return $groups->first();
                        });
                    }),

                Tables\Actions\Action::make('setting')
                    ->label('Pengaturan')
                    ->hidden($groups->isEmpty())
                    ->successNotificationTitle('Pengaturan kelompok berhasil disimpan')
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('group_max_members')
                            ->label('Maksimal anggota kelompok')
                            ->default($this->ownerRecord->group_max_members)
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(fn() => $this->ownerRecord->students()->count()),

                        Forms\Components\Checkbox::make('student_can_manage_group')
                            ->label('Mahasiswa dapat mengelola kelompok')
                            ->default($this->ownerRecord->student_can_manage_group),
                    ]))
                    ->action(function (array $data, Tables\Actions\Action $action) {
                        return DB::transaction(function () use ($data, $action) {
                            $maxMembers = $data['group_max_members'];

                            if ($maxMembers < $this->ownerRecord->group_max_members) {
                                $this->ownerRecord
                                    ->groups()
                                    ->with('subjectGroupMembers')
                                    ->get()
                                    ->each(function (SubjectGroup $group) use ($maxMembers) {
                                        if ($group->subjectGroupMembers->count() <= $maxMembers) {
                                            return;
                                        }

                                        $group->members()->detach(
                                            $group->subjectGroupMembers->skip($maxMembers)->pluck('student_id')
                                        );
                                    });
                            }

                            $this->ownerRecord->update($data);

                            $action->success();
                        });
                    }),

                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->hidden($groups->isEmpty())
                    ->successNotificationTitle('Kelompok berhasil dibuat')
                    ->form(function (Form $form) {
                        $this->ownerRecord->students->load('account:id,name');

                        $studentOptions = $this->ownerRecord->students->mapWithKeys(fn(Student $student) => [
                            $student->id => "$student->id: {$student->account->name}",
                        ]);

                        return $form
                            ->model(SubjectGroup::class)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama kelompok')
                                    ->default(function () {
                                        $groupNames = $this->ownerRecord->groups()->pluck('name');

                                        foreach ($groupNames as $index => $groupName) {
                                            preg_match('/kelompok ([0-9]+).*/i', $groupName, $matches);

                                            $no = $index + 1;
                                            if (isset($matches[1]) && $matches[1] != $no) {
                                                return 'Kelompok ' . $no;
                                            }
                                        }

                                        return 'Kelompok ' . ($groupNames->count() + 1);
                                    })
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('students')
                                    ->label('Anggota kelompok')
                                    ->multiple()
                                    ->optionsLimit(100)
                                    ->options($studentOptions)
                                    ->maxItems($this->ownerRecord->group_max_members),
                            ]);
                    })
                    ->successNotificationTitle('Kelompok berhasil dibuat')
                    ->using(fn(array $data) => DB::transaction(function () use ($data) {
                        $group = $this->ownerRecord->groups()->create([
                            'name' => str($data['name'])->lower()->startsWith('kelompok')
                                ? $data['name']
                                : "Kelompok $data[name]",
                        ]);

                        if (! empty($data['students'])) {
                            $group->members()->attach($data['students']);
                        }

                        return $group;
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(SubjectGroup $record) => SubjectResource::getUrl('group', [
                        $this->ownerRecord, $record,
                    ])),
                //                Tables\Actions\Action::make('view')
                //                    ->label('Lihat')
                //                    ->color('gray')
                //                    ->icon(FilamentIcon::resolve('actions::view-action') ?? 'heroicon-m-eye')
                //                    ->modalSubmitAction(false)
                //                    ->modalCancelAction(false)
                //                    ->modalHeading(fn(SubjectGroup $record) => 'Anggota ' . $record->name)
                //                    ->modalContent(function (SubjectGroup $record) {
                //                        return new HtmlString(Blade::render('@livewire($widgets, $props)', [
                //                            'widgets' => SubjectResource\Widgets\GroupTableWidget::class,
                //                            'props'   => [
                //                                'group' => $record,
                //                            ],
                //                        ]));
                //                    }),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading(fn(SubjectGroup $record) => 'Hapus ' . Str::lower($record->name))
                    ->modalSubmitActionLabel('Hapus')
                    ->modalDescription(fn() => new HtmlString(
                        'Apakah Anda yakin ingin melakukan ini?<br>' .
                        'Data pengumpulan tugas akan tetap tersimpan'
                    ))
                    ->successNotificationTitle('Kelompok yang dipilih berhasil dihapus'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus yang dipilih')
                    ->successNotificationTitle('Kelompok yang dipilih berhasil dihapus')
                    ->using(function (EloquentCollection $records) {
                        $records->each(function (SubjectGroup $record) {
                            $record->members->each->delete();
                            $record->delete();
                        });
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount('subjectGroupMembers');
            });
    }

    protected function canCreate(): bool
    {
        return Gate::forUser(Filament::auth()->user())
            ->authorize('create', [SubjectGroup::class, $this->ownerRecord])
            ->allowed();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}

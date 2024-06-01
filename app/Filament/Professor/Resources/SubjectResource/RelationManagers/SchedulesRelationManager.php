<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Models\SubjectSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Absensi';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('meeting_no')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn(SubjectSchedule $record) => 'Pertemuan ke ' . $record->meeting_no)
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('meeting_no')
                    ->formatStateUsing(function (SubjectSchedule $record) {
                        $text = "Pertemuan ke $record->meeting_no";

                        if ($record->start_time->isFuture()) {
                            return new HtmlString('
                                <div
                                    title="Pertemuan ini belum dimulai"
                                    class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500"
                                >
                                    ' . $text . '
                                </div>
                            ');
                        }

                        return $text;
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('subject:id,slug');
            })
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Absen')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->hidden(fn(SubjectSchedule $record) => $record->start_time->isFuture())
                    ->url(fn(SubjectSchedule $record) => SubjectResource::getUrl('attendance', [
                        $record->subject,
                        $record->meeting_no,
                    ])),
            ])
            ->recordUrl(function (SubjectSchedule $record) {
                if ($record->start_time->isFuture()) {
                    return null;
                }

                return SubjectResource::getUrl('attendance', [
                    $record->subject,
                    $record->meeting_no,
                ]);
            });
    }
}

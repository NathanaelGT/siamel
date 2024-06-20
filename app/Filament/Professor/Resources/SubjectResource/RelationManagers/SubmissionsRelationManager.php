<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Enums\AssignmentType;
use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Models\Post;
use App\Models\Subject;
use App\Models\Submission;
use Carbon\CarbonInterface;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @property-read \App\Models\Post $ownerRecord */
class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Pengumpulan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('submitter_title')
            ->defaultSort('updated_at')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('submissionable_id')
                    ->label('NPM')
                    ->visible($this->ownerRecord->assignment->type === AssignmentType::Individual),

                Tables\Columns\TextColumn::make('submissionable.name')
                    ->label($this->ownerRecord->assignment->type === AssignmentType::Individual ? 'Nama' : 'Kelompok'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir diubah')
                    ->color(fn(Submission $record) => $record->updated_at->gt($this->ownerRecord->assignment->deadline)
                        ? 'danger'
                        : null
                    )
                    ->tooltip(function (Submission $record) {
                        if ($record->updated_at->gt($this->ownerRecord->assignment->deadline)) {
                            $diff = $record->updated_at->diffForHumans(
                                $this->ownerRecord->assignment->deadline,
                                syntax: CarbonInterface::DIFF_ABSOLUTE,
                                parts: 7
                            );

                            return "Telat $diff";
                        }

                        return null;
                    })
                    ->dateTime(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->placeholder('Belum dinilai'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Submission $record) => SubjectResource::getUrl('submission', [
                        once(fn() => Subject::query()
                            ->where('id', $this->ownerRecord->subject_id)
                            ->value('slug')),
                        $this->ownerRecord,
                        $record,
                    ])),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->ownerRecord->assignment->type === AssignmentType::Individual) {
                    $query->with(['submissionable.account:id,name']);
                }
            });
    }

    /** @param  Submission  $record */
    protected function canView(Model $record): bool
    {
        static $cache = [];
        if (! isset($cache[$record->assignment_id])) {
            $cache[$record->assignment_id] = Post::query()
                ->where('id', $record->assignment_id)
                ->value('subject_id');
        }

        return $cache[$record->assignment_id] = $this->ownerRecord->subject_id;
    }
}

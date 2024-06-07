<?php

namespace App\Filament\Student\Resources\SubjectResource\Widgets;

use App\Models\SubjectGroup;
use App\Models\SubjectGroupMember;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class SubjectGroupMemberTable extends BaseWidget
{
    #[Locked]
    public SubjectGroup $group;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.account.name')
            ->paginated(false)
            ->heading('Anggota ' . Str::lower($this->group->name))
            ->emptyStateHeading('Belum ada anggota')
            ->headerActions([
                Tables\Actions\Action::make('close')
                    ->label('Tutup')
                    ->hiddenLabel()
                    ->icon('heroicon-m-x-mark')
                    ->iconButton()
                    ->color('gray')
                    ->size(ActionSize::Large)
                    ->alpineClickHandler('1') // biar click handlernya bisa diset secara dinamis
                    ->extraAttributes([
                        'title'  => 'Tutup',
                        'x-init' => '$el.setAttribute(`x-on:click`, $root.parentElement.parentElement.parentElement.getAttribute(`x-on:keydown.window.escape`))',
                    ]),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('student.id'),

                Tables\Columns\TextColumn::make('student.account.name'),
            ])
            ->query(SubjectGroupMember::query()->where('subject_group_id', $this->group->id));
    }
}

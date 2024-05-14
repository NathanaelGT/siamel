<?php

namespace App\Filament\Staff\Resources\StaffResource\Pages;

use App\Filament\Staff\Resources\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    /** @param  \App\Models\Staff  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->updateData($data);

        return $record;
    }

    protected function resolveRecord(int | string $key): Model
    {
        return parent::resolveRecord($key)
            ->load('account');
    }

    protected function fillForm(): void
    {
        $state = $this->getRecord()->toArray();

        $this->form->fill($state);
    }

    protected function refreshFormData(array $attributes): void
    {
        $this->data = [
            ...$this->data,
            ...Arr::only($this->getRecord()->toArray(), $attributes),
        ];
    }
}

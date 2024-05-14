<?php

namespace App\Filament\Staff\Resources\StaffResource\Pages;

use App\Filament\Staff\Resources\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewStaff extends ViewRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
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
}

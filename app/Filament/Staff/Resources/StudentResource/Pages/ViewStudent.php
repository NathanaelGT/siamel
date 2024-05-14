<?php

namespace App\Filament\Staff\Resources\StudentResource\Pages;

use App\Filament\Staff\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

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

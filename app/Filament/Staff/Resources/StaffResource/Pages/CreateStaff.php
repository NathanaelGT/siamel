<?php

namespace App\Filament\Staff\Resources\StaffResource\Pages;

use App\Filament\Staff\Resources\StaffResource;
use App\Service\Auth\CreateAccount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return CreateAccount::staff($data);
    }
}

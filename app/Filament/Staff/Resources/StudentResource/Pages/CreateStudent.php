<?php

namespace App\Filament\Staff\Resources\StudentResource\Pages;

use App\Filament\Staff\Resources\StudentResource;
use App\Service\Auth\CreateAccount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return CreateAccount::student($data);
    }
}

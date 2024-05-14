<?php

namespace App\Filament\Staff\Resources\ProfessorResource\Pages;

use App\Filament\Staff\Resources\ProfessorResource;
use App\Service\Auth\CreateAccount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProfessor extends CreateRecord
{
    protected static string $resource = ProfessorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return CreateAccount::professor($data);
    }
}

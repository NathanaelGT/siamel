<?php

namespace App\Filament\Staff\Resources\StudentResource\Pages;

use App\Filament\Staff\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        if (Auth::user()->faculty_id !== null) {
            return parent::paginateTableQuery($query);
        }

        return $query->simplePaginate($this->getTableRecordsPerPage());
    }
}

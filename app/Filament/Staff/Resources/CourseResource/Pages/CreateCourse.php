<?php

namespace App\Filament\Staff\Resources\CourseResource\Pages;

use App\Filament\Staff\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}

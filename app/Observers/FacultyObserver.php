<?php

namespace App\Observers;

use App\Models\Faculty;
use Illuminate\Support\Str;

class FacultyObserver
{
    public function creating(Faculty $faculty): void
    {
        $faculty->forceFill([
            'slug' => Str::slug($faculty->name, language: null, dictionary: []),
        ]);
    }
}

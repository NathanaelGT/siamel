<?php

namespace App\Observers;

use App\Models\StudyProgram;
use Illuminate\Support\Str;

class StudyProgramObserver
{
    public function creating(StudyProgram $studyProgram): void
    {
        $studyProgram->forceFill([
            'relative_id' => StudyProgram::query()
                    ->where('faculty_id', $studyProgram->faculty_id)
                    ->count() + 1,

            'slug' => Str::slug($studyProgram->name, language: null, dictionary: []),
        ]);
    }
}

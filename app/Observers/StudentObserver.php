<?php

namespace App\Observers;

use App\Models\Student;

class StudentObserver
{
    public function creating(Student $student): void
    {
        $student->parent_phone = normalize_phone_number($student->parent_phone);

        if (blank($student->id)) {
            $student->loadMissing('studyProgram:id,faculty_id,level');

            $pad = function (int $num, int $length = 2): string {
                return str_pad($num, $length, 0, STR_PAD_LEFT);
            };

            $idPattern = (int) implode([
                substr(now()->year, -2),
                $pad($student->studyProgram->faculty_id),
                $pad($student->study_program_id),
                $pad($student->studyProgram->level->getId()),
            ]);

            $lastId = Student::query()
                ->where('id', 'like', "$idPattern%")
                ->orderBy('id', 'desc')
                ->value('id');

            if ($lastId) {
                $id = $lastId + 1;
            } else {
                $id = intval($idPattern . '0001');
            }

            $student->forceFill(['id' => $id]);
        }
    }

    public function updating(Student $student): void
    {
        if ($student->isDirty('parent_phone')) {
            $student->parent_phone = normalize_phone_number($student->parent_phone);
        }
    }
}

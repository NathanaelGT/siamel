<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Post;
use App\Models\Professor;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return match ($user->role) {
            Role::Professor, Role::Student => true,
            default                        => false,
        };
    }

    public function view(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            Role::Professor => $this->professorCanAccess($user->professor, $submission),
            Role::Student   => $this->studentCanAccess($user->student, $submission),
            default         => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Student;
    }

    public function update(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            Role::Student => $this->studentCanAccess($user->student, $submission),
            default       => false,
        };
    }

    public function delete(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            default     => false,
        };
    }

    public function restore(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            default     => false,
        };
    }

    public function forceDelete(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            default     => false,
        };
    }

    protected function professorCanAccess(Professor $professor, Submission $submission): bool
    {
        return Subject::query()
            ->where('id', Post::query()
                ->where('id', $submission->assignment_id)
                ->select('subject_id')
                ->limit(1))
            ->where('professor_id', $professor->id)
            ->exists();
    }

    protected function studentCanAccess(Student $student, Submission $submission): bool
    {
        if ($submission->submissionable->is($student)) {
            return true;
        }

        return $submission->submissionable
            ->subjectGroupMembers()
            ->where('student_id', $student->id)
            ->exists();
    }
}

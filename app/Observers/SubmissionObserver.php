<?php

namespace App\Observers;

use App\Enums\AssignmentType;
use App\Enums\Role;
use App\Exceptions\InvalidRoleException;
use App\Models\Post;
use App\Models\SubjectGroup;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;

class SubmissionObserver
{
    public function creating(Submission $submission): void
    {
        $user = auth()->user();
        if ($user->role !== Role::Student) {
            throw new InvalidRoleException(expect: Role::Student, actual: $user->role);
        }

        $assignmentType = $submission->relationLoaded('assignment')
            ? $submission->assignment->type
            : $submission->assignment()->value('type');

        if ($assignmentType === AssignmentType::Individual) {
            $submission->forceFill([
                'submissionable_type' => $user->role->model(),
                'submissionable_id'   => $user->info_id,
            ]);
        } else {
            $submission->forceFill([
                'submissionable_type' => SubjectGroup::class,
                'submissionable_id'   => SubjectGroup::query()
                    ->where('subject_id', Post::query()
                        ->where('id', $submission->assignment_id)
                        ->limit(1)
                        ->select('subject_id'))
                    ->whereExists(function (Builder $query) use ($user) {
                        $query->from('subject_group_members')
                            ->whereColumn('subject_group_members.subject_group_id', 'subject_groups.id')
                            ->where('student_id', $user->info_id);
                    })
                    ->value('id'),
            ]);
        }

        $now = now()->toDateTimeString();

        $submission->forceFill([
            'submitted_at' => $now,
            'updated_at'   => $now,
        ]);
    }
}

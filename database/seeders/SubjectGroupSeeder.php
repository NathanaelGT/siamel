<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class SubjectGroupSeeder extends Seeder
{
    public function setupRun(): void
    {
        foreach (Semester::pluck('id') as $semesterId) {
            $this->dispatcher->run($semesterId);
        }
    }

    public function run(int $semesterId): void
    {
        $professorIds = DB::table('subjects')
            ->distinct()
            ->where('semester_id', $semesterId)
            ->pluck('professor_id');

        $professorCount = $professorIds->count();
        $randomProfessorIds = $professorIds->random(
            $this->faker->numberBetween($professorCount / 10, $professorCount / 3)
        );

        $subjectIds = DB::table('subjects')
            ->where('semester_id', $semesterId)
            ->whereIn('professor_id', $randomProfessorIds->all())
            ->pluck('id');

        $studentSubjectMap = DB::table('student_subject')
            ->whereIn('subject_id', $subjectIds->all())
            ->get(['student_id', 'subject_id'])
            ->groupBy('subject_id');

        $subjectCount = $subjectIds->count();
        $studentCanManageGroupSubjectIds = $subjectIds->random(
            $this->faker->numberBetween($subjectCount / 3, $subjectCount / 2)
        );
        $studentCanCreateGroupSubjectIds = $subjectIds->random(
            $this->faker->numberBetween($subjectCount / 3, $subjectCount / 2)
        );

        $now = now()->toDateTimeString();

        $groupMaxMemberMap = [];

        $groups = [];
        $groupMembers = [];
        foreach ($subjectIds as $subjectId) {
            if (! isset($studentSubjectMap[$subjectId])) {
                continue;
            }

            $groupMaxMembers = $this->faker->numberBetween(2, 5);
            $groupMaxMemberMap[$groupMaxMembers][] = $subjectId;

            $studentIds = $studentSubjectMap[$subjectId]
                ->pluck('student_id')
                ->chunk($groupMaxMembers);

            foreach ($studentIds as $index => $groupMemberIds) {
                $groupId = $this->groupId($subjectId, $index);

                $groups[] = [
                    'id'         => $groupId,
                    'name'       => 'Kelompok ' . ($index + 1),
                    'subject_id' => $subjectId,
                    'created_at' => $now,
                ];

                foreach ($groupMemberIds as $groupMemberId) {
                    $groupMembers[] = [
                        'subject_group_id' => $groupId,
                        'student_id'       => $groupMemberId,
                    ];
                }
            }
        }

        foreach ($groupMaxMemberMap as $groupMaxMembers => $maxMemberSubjectIds) {
            DB::table('subjects')
                ->whereIn('id', $maxMemberSubjectIds)
                ->update(['group_max_members' => $groupMaxMembers]);
        }

        DB::table('subjects')
            ->whereIn('id', $studentCanManageGroupSubjectIds->all())
            ->update(['student_can_manage_group' => true]);

        DB::table('subjects')
            ->whereIn('id', $studentCanCreateGroupSubjectIds->all())
            ->update([
                'student_can_manage_group' => true,
                'student_can_create_group' => true,
            ]);

        foreach (array_chunk($groups, 4500) as $chunk) {
            DB::table('subject_groups')->insert($chunk);
        }
        foreach (array_chunk($groupMembers, 6000) as $chunk) {
            DB::table('subject_group_members')->insert($chunk);
        }
    }

    protected function groupId(int $subjectId, int $groupIndex): int
    {
        return intval($subjectId . str_pad($groupIndex, 2, 0, STR_PAD_LEFT));
    }
}

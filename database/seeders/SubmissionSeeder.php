<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\SubjectGroup;
use DateInterval;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class SubmissionSeeder extends Seeder
{
    public function setupRun(): void
    {
        foreach (DB::table('semesters')->pluck('id') as $semesterId) {
            $this->dispatcher->run($semesterId);
        }
    }

    public function afterRun(): void
    {
        DB::table('submissions')
            ->where('scored_at', '>', now()->subSeconds($this->faker->numberBetween(1, 5e5)))
            ->update([
                'score'     => null,
                'scored_at' => null,
            ]);
    }

    public function run(int $semesterId): void
    {
        $assignments = DB::table('assignments')
            ->join('posts', 'assignments.id', '=', 'posts.id')
            ->join('subjects', function (JoinClause $join) use ($semesterId) {
                $join->on('posts.subject_id', '=', 'subjects.id')
                    ->where('semester_id', $semesterId);
            })
            ->get(['assignments.id', 'assignments.deadline', 'posts.published_at', 'posts.subject_id']);

        $subjectGroupsMap = DB::table('subject_groups')
            ->whereIn('subject_id', $assignments->pluck('subject_id')->all())
            ->get(['id', 'subject_id'])
            ->reduce(function (array $subjectGroupsMap, stdClass $subjectGroup) {
                $subjectGroupsMap[$subjectGroup->subject_id][] = $subjectGroup->id;

                return $subjectGroupsMap;
            }, []);

        $subjectStudentsMap = DB::table('student_subject')
            ->get(['student_id', 'subject_id'])
            ->reduce(function (array $subjectStudentsMap, stdClass $studentSubject) {
                $subjectStudentsMap[$studentSubject->subject_id][] = $studentSubject->student_id;

                return $subjectStudentsMap;
            }, []);

        $submissions = [];
        foreach ($assignments as $assignment) {
            $groupIds = $subjectGroupsMap[$assignment->subject_id] ?? null;
            $studentIds = $subjectStudentsMap[$assignment->subject_id] ?? null;

            if ($groupIds !== null) {
                foreach ($groupIds as $groupId) {
                    if ($this->faker->boolean(10)) {
                        continue;
                    }

                    $exceedDeadline = $this->faker->boolean(15);

                    $submittedAt = $this->faker->dateTimeBetween(
                        $assignment->published_at,
                        $exceedDeadline
                            ? $assignment->deadline
                            : Carbon::parse($assignment->deadline)
                            ->addSeconds($this->faker->numberBetween(0, 3e5)),
                    );

                    $submittedAtString = $submittedAt->format('Y-m-d H:i:s');

                    $submissions[] = [
                        'submissionable_type' => SubjectGroup::class,
                        'submissionable_id'   => $groupId,
                        'assignment_id'       => $assignment->id,
                        'note'                => '',
                        'score'               => $this->faker->boolean(10)
                            ? $this->faker->numberBetween(0, 60)
                            : $this->faker->numberBetween(61, 100),
                        'submitted_at'        => $submittedAtString,
                        'scored_at'           => $submittedAt
                            ->add(new DateInterval('PT' . $this->faker->numberBetween(1e5, 2e6) . 'S'))
                            ->format('Y-m-d H:i:s'),
                        'updated_at'          => $submittedAtString,
                    ];
                }
            } elseif ($studentIds !== null) {
                foreach ($studentIds as $studentId) {
                    if ($this->faker->boolean(15)) {
                        continue;
                    }

                    $exceedDeadline = $this->faker->boolean(10);

                    $submittedAt = $this->faker->dateTimeBetween(
                        $assignment->published_at,
                        $exceedDeadline
                            ? $assignment->deadline
                            : Carbon::parse($assignment->deadline)
                            ->addSeconds($this->faker->numberBetween(0, 3e5)),
                    );

                    $submittedAtString = $submittedAt->format('Y-m-d H:i:s');

                    $submissions[] = [
                        'submissionable_type' => Student::class,
                        'submissionable_id'   => $studentId,
                        'assignment_id'       => $assignment->id,
                        'note'                => '',
                        'score'               => $this->faker->boolean(10)
                            ? $this->faker->numberBetween(0, 60)
                            : $this->faker->numberBetween(61, 100),
                        'submitted_at'        => $submittedAtString,
                        'scored_at'           => $submittedAt
                            ->add(new DateInterval('PT' . $this->faker->numberBetween(1e5, 2e6) . 'S'))
                            ->format('Y-m-d H:i:s'),
                        'updated_at'          => $submittedAtString,
                    ];
                }
            }
        }

        foreach (array_chunk($submissions, 4000) as $chunk) {
            DB::table('submissions')->insert($chunk);
        }
    }
}

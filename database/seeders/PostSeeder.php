<?php

namespace Database\Seeders;

use App\Enums\AssignmentCategory;
use App\Enums\AssignmentType;
use App\Enums\PostType;
use App\Enums\WorkingDay;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function setupRun(): void
    {
        foreach (DB::table('semesters')->pluck('id') as $semesterId) {
            $this->dispatcher->run($semesterId);
        }
    }

    public function run(int $semesterId): void
    {
        $query = DB::table('semester_schedules')
            ->where('semester_id', $semesterId)
            ->where('name', 'Perkuliahan');

        $firstDay = Carbon::create($query->value('date'));
        $lastDay = $query->orderBy('date', 'desc')->value('date');
        $scheduleCount = $firstDay->diffInWeeks($lastDay);

        $subjects = DB::table('subjects')
            ->join('professors', 'subjects.professor_id', '=', 'professors.id')
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->where('subjects.semester_id', $semesterId)
            ->get([
                'subjects.id',
                'users.id as professor_account_id',
                'subjects.slug',
                'subjects.day',
                'subjects.start_time',
            ]);

        $posts = [];
        $assignments = [];
        $attachments = [];

        $count = 0;
        foreach ($subjects as $subject) {
            $date = $firstDay->clone();
            $postCount = [
                PostType::Assignment->name       => 0,
                PostType::LearningMaterial->name => 0,
            ];

            for ($i = 0; $i < $scheduleCount; $i++) {
                $date->addWeek();

                if ($this->faker->boolean(20)) {
                    continue;
                }

                $exactTime = $date->setTimeFrom($subject->start_time)->addDays($max = match ($subject->day) {
                    WorkingDay::Monday->value    => 0,
                    WorkingDay::Tuesday->value   => 1,
                    WorkingDay::Wednesday->value => 2,
                    WorkingDay::Thursday->value  => 3,
                    WorkingDay::Friday->value    => 4,
                });

                $isEarlyPost = $this->faker->boolean(20);
                $howEarly = $isEarlyPost ? $this->faker->numberBetween(0, $max) : 0;

                $createdAt = $exactTime->clone()->subDays($howEarly)->toDateString() . ' ' .
                    mt_rand(7, 22) . ':' .
                    mt_rand(0, 59) . ':' .
                    mt_rand(0, 59);

                $isScheduledPost = $this->faker->boolean(10);

                $publishedAt = $isScheduledPost
                    ? $exactTime->toDateTimeString()
                    : $createdAt;

                $type = $this->faker->boolean(80)
                    ? PostType::LearningMaterial
                    : PostType::Assignment;

                $postCount[$type->name]++;

                $postId = $semesterId * 100000 + $count++;

                if ($type === PostType::Assignment) {
                    $assignments[] = [
                        'id'       => $postId,
                        'type'     => AssignmentType::Individual->value,
                        'category' => $category = match ($i) {
                            $scheduleCount - 1         => AssignmentCategory::Final->value,
                            intval($scheduleCount / 2) => AssignmentCategory::Midterm->value,
                            default                    => $this->faker->randomElement([
                                AssignmentCategory::Homework->value,
                                AssignmentCategory::Quiz->value,
                                AssignmentCategory::Project->value,
                            ]),
                        },
                        'mimes'    => '[]',
                        'deadline' => Carbon::create($publishedAt)
                            ->addWeeks($this->faker->numberBetween(1, 2))
                            ->endOfDay()
                            ->toDateTimeString(),
                    ];
                } else {
                    $category = null;
                }

                $posts[] = [
                    'id'           => $postId,
                    'subject_id'   => $subject->id,
                    'user_id'      => $subject->professor_account_id,
                    'title'        => $this->title($type, $postCount[$type->name], $category),
                    'content'      => $this->content($type),
                    'type'         => $type->value,
                    'published_at' => $publishedAt,
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ];

                $attachments[] = [
                    'attachmentable_type' => Post::class,
                    'attachmentable_id'   => $postId,
                    'owner_id'            => $subject->professor_account_id,
                    'name'                => lcfirst($type->value) . ' ' . $postCount[$type->name] . '.pdf',
                    'path'                => 'attachment.pdf',
                    'slug'                => "subject/$subject->slug/$type->value {$postCount[$type->name]}.pdf",
                ];
            }
        }

        foreach (array_chunk($posts, 4000) as $chunk) {
            DB::table('posts')->insert($chunk);
        }
        foreach (array_chunk($assignments, 4000) as $chunk) {
            DB::table('assignments')->insert($chunk);
        }
        foreach (array_chunk($attachments, 3500) as $chunk) {
            DB::table('attachments')->insert($chunk);
        }
    }

    protected function title(PostType $type, int $number, ?string $category): string
    {
        if ($category) {
            return $category;
        }

        if ($this->faker->boolean(40)) {
            return $type->value . ' ' . $number;
        } elseif ($this->faker->boolean(40)) {
            return $type->value . ' ke-' . $number;
        } elseif ($this->faker->boolean(30)) {
            return $type->value . ' ke' . $number;
        }

        $word = match ($number) {
            1       => 'Pertama',
            2       => 'Kedua',
            3       => 'Ketiga',
            4       => 'Keempat',
            5       => 'Kelima',
            6       => 'Keenam',
            7       => 'Ketujuh',
            8       => 'Kedelapan',
            9       => 'Kesembilan',
            10      => 'Kesepuluh',
            11      => 'Kesebelas',
            12      => 'Kedua Belas',
            13      => 'Ketiga Belas',
            14      => 'Keempat Belas',
            15      => 'Kelima Belas',
            16      => 'Keenam Belas',
            17      => 'Ketujuh Belas',
            18      => 'Kedelapan Belas',
            default => 'Ke-' . $number,
        };

        if ($this->faker->boolean(40)) {
            return $type->value . ' ' . lcfirst($word);
        }

        return $type->value . ' ' . $word;
    }

    protected function content(PostType $type): string
    {
        if ($this->faker->boolean(80)) {
            return '';
        }

        if ($type === PostType::LearningMaterial) {
            $content = $this->faker->randomElement([
                'Harap dibaca dengan seksama',
                'Mohon dibaca dengan seksama',
                'Perhatikan dengan baik',
                'Mohon diperhatikan',
            ]);
        } else {
            $content = $this->faker->randomElement([
                'Harap segera dikerjakan',
                'Mohon segera diselesaikan',
                'Mohon segera dikerjakan',
                'Mohon segera diunggah',
                'Kerjakan dengan seksama',
            ]);
        }

        if ($this->faker->boolean()) {
            return lcfirst($content);
        }

        return $content;
    }
}

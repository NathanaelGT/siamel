<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\StudyProgram;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        StudyProgram::unguarded(fn() => StudyProgram::withoutEvents(function () {
            $now = now()->addSeconds(2)->toDateTimeString();

            $courses = [];
            $rooms = [];

            foreach (FacultyDataset::get() as $faculty) {
                Faculty::factory()
                    ->has(
                        StudyProgram::factory()
                            ->forEachSequence(
                                ...$faculty->studyPrograms
                                ->select(['relative_id', 'name', 'level'])
                                ->toArray()
                            )
                            ->afterCreating(function (StudyProgram $studyProgram) use (&$courses, $faculty, $now) {
                                static $i = 0;

                                $courses[] = $faculty->studyPrograms
                                    ->get($i++)
                                    ->courses
                                    ->map(function (Arrayable $data) use ($studyProgram, $now) {
                                        $course = $data->toArray();
                                        $course['study_program_id'] = $studyProgram->id;
                                        $course['created_at'] = $now;

                                        return $course;
                                    })
                                    ->all();
                            })
                    )
                    ->has(
                        Building::factory()
                            ->forEachSequence(...$faculty->buildings->toArray())
                            ->afterCreating(function (Building $building) use (&$rooms, $faculty) {
                                static $i = 0;

                                $rooms[] = $faculty->buildings
                                    ->get($i++)
                                    ->rooms
                                    ->map(function (Arrayable $data) use ($building) {
                                        $room = $data->toArray();
                                        $room['building_id'] = $building->id;

                                        return $room;
                                    })
                                    ->all();
                            })
                    )
                    ->create($faculty->toArray());
            }

            Course::query()->insert(Arr::collapse($courses));
            Room::query()->insert(Arr::collapse($rooms));
        }));
    }
}

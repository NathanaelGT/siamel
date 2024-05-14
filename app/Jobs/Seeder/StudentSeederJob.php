<?php

namespace App\Jobs\Seeder;

use Database\Seeders\Data\FacultyData;
use Database\Seeders\StudentSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StudentSeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected FacultyData $faculty,
        protected int $year,
        protected array $userIds,
        protected array $userCounts
    )
    {
    }

    public function handle(): void
    {
        (new StudentSeeder)([$this->faculty, $this->year, $this->userIds, $this->userCounts]);
    }
}

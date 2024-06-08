<?php

namespace App\Providers;

use App\Enums\SemesterSchedules;
use App\Models\Semester;
use App\Models\User;
use App\Period\Period;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->definePeriodGate();
    }

    protected function definePeriodGate(): void
    {
        Gate::define(Period::Learning, fn(?User $user) => once(function () {
            $period = Semester::current()
                ->schedules()
                ->whereIn('name', [
                    SemesterSchedules::Normal,
                    SemesterSchedules::Midterm,
                    SemesterSchedules::Final,
                ])
                ->toBase()
                ->first([
                    DB::raw('min(`date`) as `min`'),
                    DB::raw('max(`date`) as `max`'),
                ]);

            return now()->isBetween(
                $period->min,
                Carbon::parse($period->max)->endOfDay(),
            );
        }));

        Gate::define(Period::KRS, fn(?User $user) => once(function () {
            $period = Semester::current()
                ->schedules()
                ->where('name', SemesterSchedules::KRS)
                ->toBase()
                ->first([
                    DB::raw('min(`date`) as `min`'),
                    DB::raw('max(`date`) as `max`'),
                ]);

            return now()->isBetween(
                $period->min,
                Carbon::parse($period->max)->addWeek()->endOfWeek(),
            );
        }));
    }
}

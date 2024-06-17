<?php

namespace App\Providers;

use App\Enums\SemesterSchedules;
use App\Models\Semester;
use App\Models\User;
use App\Period\Period;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureVerifyEmailUrl();
        $this->definePeriodGate();
    }

    protected function configureVerifyEmailUrl(): void
    {
        VerifyEmail::createUrlUsing(Filament::getVerifyEmailUrl(...));
    }

    protected function definePeriodGate(): void
    {
        $periodCallback = fn(Closure $callback) => function (?User $user) use ($callback) {
            return once(function () use ($user, $callback) {
                if (config('siamel.period.bypass_all_gates')) {
                    return true;
                }

                return $callback($user) ?: Response::denyAsNotFound();
            });
        };

        Gate::define(Period::Learning, $periodCallback(function () {
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


        $krsPeriod = fn() => once(fn() => Semester::current()
            ->schedules()
            ->where('name', SemesterSchedules::KRS)
            ->toBase()
            ->first([
                DB::raw('min(`date`) as `min`'),
                DB::raw('max(`date`) as `max`'),
            ]));

        Gate::define(Period::KRS, $periodCallback(function () use ($krsPeriod) {
            $period = $krsPeriod();

            return now()->isBetween(
                $period->min,
                Carbon::parse($period->max)->addWeek()->endOfWeek(),
            );
        }));

        Gate::define(Period::KRSPreparation, $periodCallback(function () use ($krsPeriod) {
            $period = $krsPeriod();

            return now()->isBetween(
                Carbon::parse($period->min)->subWeeks(2)->endOfWeek(),
                Carbon::parse($period->max)->addWeek()->endOfWeek(),
            );
        }));
    }
}

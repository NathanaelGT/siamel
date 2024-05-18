<?php

namespace App\Models;

use App\Enums\Parity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'parity',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'parity' => Parity::class,
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SemesterSchedule::class);
    }

    public function scopeCurrent(Builder $query): void
    {
        $parities = [
            8  => Parity::Odd,
            9  => Parity::Odd,
            10 => Parity::Odd,
            11 => Parity::Odd,
            12 => Parity::Odd,
            1  => Parity::Odd,
            2  => Parity::Even,
            3  => Parity::Even,
            4  => Parity::Even,
            5  => Parity::Even,
            6  => Parity::Even,
            7  => Parity::Even,
        ];

        $query
            ->where('year', now()->year)
            ->where('parity', $parities[now()->month])
            ->limit(1);
    }

    public static function current(): ?static
    {
        static $current = self::query()->current()->first();

        return $current;
    }
}

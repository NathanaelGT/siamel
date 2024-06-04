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
            'year'   => 'int',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SemesterSchedule::class);
    }

    public function scopeCurrent(Builder $query): void
    {
        $now = now();

        $parity = match ($now->month) {
            8, 9, 10, 11, 12, 1 => Parity::Odd,
            2, 3, 4, 5, 6, 7    => Parity::Even,
        };

        $query
            ->where('year', $now->year)
            ->where('parity', $parity)
            ->limit(1);
    }

    public static function current(): ?static
    {
        static $current = self::query()->current()->first();

        return $current;
    }
}

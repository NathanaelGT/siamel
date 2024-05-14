<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fn(array $attributes) => (string) str(
                $this->faker->unique()->name(strtolower($attributes['gender']->name))
            )
                ->before('.')
                ->beforeLast(' '),

            'email' => fn(array $attributes) => (string) str(
                str($attributes['name'])
                    ->explode(' ')
                    ->when($this->faker->boolean(35))->shuffle()
                    ->take($this->faker->numberBetween(
                        3,
                        intval(str($attributes['name'])->substrCount(' ') * 1.4)
                    ))
                    ->when(
                        fn(Collection $email) => $email->count() < 2,
                        fn(Collection $email) => $email->push(' ' . strtolower($this->faker->city()))
                            ->when(
                                $this->faker->boolean(95),
                                function (Collection $email) {
                                    $email->push($this->faker->numberBetween(0, 999));
                                }
                            )
                    )
                    ->push($this->faker->numberBetween(0, 9999))
                    ->join(' ')
            )
                ->when($this->faker->boolean(70))->append(' ')
                ->when($this->faker->boolean(35), function (Stringable $email) {
                    $email->append($this->faker->safeColorName());
                })
                ->when($this->faker->boolean(65), function (Stringable $email) {
                    $email->append($this->faker->year());
                })
                ->snake($this->faker->randomElement(['_', '-', '.', '']))
                ->append('@' . $this->faker->freeEmailDomain()),

            'phone_number'      => '08' . match ($this->faker->numberBetween(1, 12)) {
                    1       => sprintf('%09d', mt_rand(1, 999999999)),
                    2, 3    => sprintf('%010d', mt_rand(1, 9999999999)),
                    4, 5, 6 => sprintf('%011d', mt_rand(1, 99999999999)),
                    default => sprintf('%012d', mt_rand(1, 999999999999)),
                },
            'gender'            => $this->faker->randomElement(Gender::class),
            'email_verified_at' => fn() => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => fn() => $this->faker->randomElement(Role::class),
            'remember_token'    => fn() => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::Admin,
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::Staff,
        ]);
    }

    public function professor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::Professor,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::Student,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

class Seeder extends BaseSeeder
{
    use WithFaker;

    public function __invoke(array $parameters = [])
    {
        $this->setUpFaker();

        return parent::__invoke($parameters);
    }

    public function callWithoutContainer(
        array | string $class,
        bool $silent = false,
        array $parameters = []
    ): void
    {
        $container = $this->container;
        $this->container = null;
        $this->call($class, $silent, $parameters);
        $this->container = $container;
    }

    protected function generateUsers(int $count, Role $role, bool $insert = true): array
    {
        $now = now()->toDateTimeString();
        $userFactory = User::factory();
        $rememberToken = Str::random(10);

        $users = [];

        $userId = User::query()->count();
        for ($i = 0; $i < $count; $i++) {
            $definition = $userFactory->definition();
            $definition['id'] = ++$userId;
            $definition['role'] = $role->name;
            $definition['remember_token'] = $rememberToken;
            $definition['email_verified_at'] = $now;
            $definition['created_at'] = $now;
            $definition['updated_at'] = $now;

            $definition['name'] = $definition['name']($definition);
            $definition['email'] = $definition['email']($definition);

            $users[] = $definition;
        }

        if ($insert) {
            User::query()->insert($users);
        }

        return $users;
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Jobs\Seeder\SeederJob;
use App\Models\User;
use Error;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** @property-read static $dispatcher */
class Seeder extends BaseSeeder
{
    use WithFaker;

    public function __get(string $name): object
    {
        $class = static::class;

        if ($name !== 'dispatcher') {
            throw new Error("Undefined property $class::\$$name.");
        }

        return new class($class) {
            public function __construct(protected string $class)
            {
                //
            }

            public function run(mixed ...$arguments): void
            {
                SeederJob::dispatch($this->class, $arguments);
            }
        };
    }

    public function __invoke(array $parameters = []): mixed
    {
        $this->setUpFaker();

        $seederJobExists = function () {
            return DB::table('jobs')->where('queue', 'seeder')->exists();
        };

        if (method_exists($this, 'setupRun') && ! $seederJobExists()) {
            $this->setupRun();

            Artisan::call('queue:work --stop-when-empty --queue=seeder --memory=2048');

            // nunggu job yang dijalanin dari worker lain
            while ($seederJobExists()) {
                usleep(500000); // 0.5 detik
            }

            if (method_exists($this, 'afterRun')) {
                return $this->afterRun();
            }

            return null;
        }

        return parent::__invoke($parameters);
    }

    public function call($class, $silent = false, array $parameters = []): static
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            if ($silent === false && isset($this->command)) {
                with(new TwoColumnDetail($this->command->getOutput()))->render(
                    $name,
                    '<fg=yellow;options=bold>RUNNING</>'
                );
            }

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            if ($silent === false && isset($this->command)) {
                $runTime = number_format((microtime(true) - $startTime) * 1000);

                with(new TwoColumnDetail($this->command->getOutput()))->render(
                    $name,
                    "<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>"
                );

                $this->command->getOutput()->writeln('');
            }

            static::$called[] = $class;
        }

        return $this;
    }

    protected function generateUsers(int $count, Role $role): array
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

            $users[] = $definition;
        }

        return $users;
    }
}

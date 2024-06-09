<?php

namespace App\Jobs\Seeder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $class,
        protected array $args,
    )
    {
        $this->queue = 'seeder';
    }

    public function handle(): void
    {
        (new $this->class)($this->args);
    }
}

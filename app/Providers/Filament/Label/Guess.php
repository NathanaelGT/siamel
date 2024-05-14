<?php

namespace App\Providers\Filament\Label;

use Illuminate\Support\Str;

readonly class Guess
{
    public function __construct(
        public string $keyWildcard,
        public string $model,
        public array $replace = [],
    )
    {
        //
    }

    public function keys(): array
    {
        return [
            Str::replaceFirst('$', $this->model, $this->keyWildcard),
            Str::replaceFirst('$', 'global', $this->keyWildcard),
        ];
    }
}

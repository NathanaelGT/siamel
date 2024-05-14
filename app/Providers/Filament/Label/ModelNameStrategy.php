<?php

namespace App\Providers\Filament\Label;

class ModelNameStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        return $column === ' __name__ ';
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return new Guess('model.$.name', $model);
    }
}

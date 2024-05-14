<?php

namespace App\Providers\Filament\Label;

class ColumnStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        return true;
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return new Guess("model.$.columns.$column", $model);
    }
}

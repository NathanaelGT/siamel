<?php

namespace App\Providers\Filament\Label;

class DefaultStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        return true;
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return static::label($column);
    }

    public static function label($column): string
    {
        return (string) str($column)->snake()->replace(['_', '.'], ' ')->ucfirst();
    }
}

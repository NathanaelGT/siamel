<?php

namespace App\Providers\Filament\Label;

use Illuminate\Support\Str;

class ModelStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        if (str_contains($column, '.')) {
            return false;
        }

        if (class_exists($model = 'App\\Models\\' . Str::studly($column))) {
            return [$model];
        }

        return false;
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return new Guess('model.$.name', $model);
    }
}

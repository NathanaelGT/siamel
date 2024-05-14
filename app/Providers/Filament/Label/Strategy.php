<?php

namespace App\Providers\Filament\Label;

abstract class Strategy
{
    public function __invoke(string $model, string $column): Guess | string | null
    {
        if ($params = $this->applicable($model, $column)) {
            if (is_array($params)) {
                $model = $params[0] ?? $model;
                $column = $params[1] ?? $column;
            }

            return $this->apply($model, $column);
        }

        return null;
    }

    protected abstract function applicable(string $model, string $column): bool | array;

    protected abstract function apply(string $model, string $column): Guess | string;
}

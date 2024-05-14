<?php

namespace App\Providers\Filament\Label;

class RelationColumnStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        $parts = explode('.', $column);
        $partCount = count($parts);

        if ($partCount < 2) {
            return false;
        }

        for ($i = 0; $i < $partCount - 1; $i++) {
            $column = $parts[$i];

            $relations = RelationStrategy::relations($model)['relations'];

            if (isset($relations[$column])) {
                $model = $relations[$column];
            } else {
                return false;
            }
        }

        return [$model, $parts[$i]];
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return new Guess("model.$.columns.$column", $model);
    }
}

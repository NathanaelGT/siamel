<?php

namespace App\Providers\Filament\Label;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Str;

class AggregateStrategy extends Strategy
{
    public function __construct(
        protected Translator $translator
    )
    {
        //
    }

    protected function applicable(string $model, string $column): bool | array
    {
        preg_match('/^(.*)_(min|max|avg|sum)_(.*)$/', $column, $matches);
        if (! empty($matches)) {
            return [$matches[2], $matches[3]];
        }

        preg_match('/^(.*)_count$/', $column, $matches);
        if (! empty($matches)) {
            return ['count', $matches[1]];
        }

        return false;
    }

    protected function apply(string $aggregate, string $column): Guess | string
    {
        $model = str($column)->singular()->studly();
        $translatedColumn = $this->translator->get($key = "model.App\\Models\\$model.name");
        if ($translatedColumn === $key) {
            $translatedColumn = DefaultStrategy::label($column);
        }

        return new Guess("model.$.aggregate.$aggregate", $model, [
            'column' => Str::lower($translatedColumn),
        ]);
    }
}

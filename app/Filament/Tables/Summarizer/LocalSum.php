<?php

namespace App\Filament\Tables\Summarizer;

use Filament\Tables\Columns\Summarizers\Summarizer;

class LocalSum extends Summarizer
{
    protected float $value = 0;

    protected float $totalValue = 0;

    protected static array $instances = [];

    public static function instance(string $id): ?static
    {
        return static::$instances[$id] ?? null;
    }

    public static function make(?string $id = null): static
    {
        $instance = parent::make($id);

        if ($id) {
            static::$instances[$id] = $instance;
        }

        return $instance;
    }

    public function increaseValue(float $state): float
    {
        $this->value += $state;
        $this->totalValue += $state;

        return $state;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getState(): float
    {
        if ($this->value === 0.0) {
            $state = $this->totalValue;
        } else {
            $state = $this->value;

            $this->value = 0;
        }

        return $state;
    }
}

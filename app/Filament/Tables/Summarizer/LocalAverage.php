<?php

namespace App\Filament\Tables\Summarizer;

use Filament\Tables\Columns\Summarizers\Summarizer;

class LocalAverage extends Summarizer
{
    protected float $value = 0;
    protected int $count = 0;

    protected float $totalValue = 0;
    protected int $totalCount = 0;

    public function increaseValue(float $value): float
    {
        $this->value += $value;
        $this->count++;

        $this->totalValue += $value;
        $this->totalCount++;

        return $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getState(): float
    {
        if ($this->count === 0) {
            $state = $this->totalValue / $this->totalCount;
        } else {
            $state = $this->value / $this->count;

            $this->value = 0;
            $this->count = 0;
        }

        return $state;
    }
}

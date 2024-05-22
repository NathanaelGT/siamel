<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerArrayMacros();
    }

    protected function registerArrayMacros(): void
    {
        Arr::macro('firstKey', function (mixed $array, ?callable $callback = null, mixed $default = null) {
            if (is_null($callback)) {
                if (empty($array)) {
                    return value($default);
                }

                foreach ($array as $key => $ignored) {
                    return $key;
                }

                return value($default);
            }

            foreach ($array as $key => $value) {
                if ($callback($value, $key)) {
                    return $key;
                }
            }

            return value($default);
        });

        Collection::macro('firstKey', function (?callable $callback = null, mixed $default = null) {
            return Arr::firstKey($this->items, $callback, $default);
        });
    }
}

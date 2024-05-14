<?php

namespace App\Filament;

use App\Providers\FilamentLabelServiceProvider;
use Filament\Resources\Resource as BaseResource;

class Resource extends BaseResource
{
    public static function getModelLabel(): string
    {
        static $cache = [];
        if (! isset($cache[static::class])) {
            $model = static::getModel();

            $cache[static::class] = static::$modelLabel ?? FilamentLabelServiceProvider::label($model);
        }

        return $cache[static::class];
    }

    public static function getSlug(): string
    {
        static $cache = [];

        if (filled(static::$slug)) {
            return static::$slug;
        }

        if (! isset($cache[static::class])) {
            $cache[static::class] = str(static::class)
                ->afterLast('\\Resources\\')
                ->beforeLast('Resource')
                ->explode('\\')
                ->map(function (string $className) {
                    return (string) str(FilamentLabelServiceProvider::label("App\\Models\\$className"))
                        ->kebab()
                        ->slug();
                })
                ->implode('/');
        }

        return $cache[static::class];
    }
}

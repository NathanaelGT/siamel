<?php

namespace App\Providers;

use App\Providers\Filament\Label\AggregateStrategy;
use App\Providers\Filament\Label\ColumnStrategy;
use App\Providers\Filament\Label\DefaultStrategy;
use App\Providers\Filament\Label\ModelNameStrategy;
use App\Providers\Filament\Label\ModelStrategy;
use App\Providers\Filament\Label\RelationColumnStrategy;
use App\Providers\Filament\Label\RelationStrategy;
use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\Component as InfolistComponent;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Throwable;

class FilamentLabelServiceProvider extends ServiceProvider
{
    protected static Filesystem $fs;

    protected static Translator $translator;

    protected static array $cache;

    protected static bool $shouldSaveCache = false;

    protected static array $time = [];

    public function boot(): void
    {
        static::$shouldSaveCache = false;

        $start = microtime(true);
        $this->configures();
        $end = microtime(true);

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function () use ($start, $end) {
                static::addMeasure('Configures Label', $start, $end);

                foreach (static::$time as $label => [$start, $end]) {
                    $elapsed = (($end - $start) / 1000000) . 'ms';
                    static::addMessage("$label: $elapsed", 'debug');

                    if (str($label)->contains('found')) {
                        static::addMessage('');
                    }
                }
            },
        );
    }

    protected function configures(): void
    {
        Field::configureUsing(function (Field $field): void {
            if ($field instanceof Select && ($relationship = $field->getRelationship()) !== null) {
                $field->label(fn() => static::measure('Label', function () use ($relationship, $field) {
                    return $this->label($relationship, $field->getRelationshipTitleAttribute());
                }));
            } else {
                $field->label(fn() => static::measure('Label', function () use ($field) {
                    return $this->label($field->getModel(), $field->getName());
                }));
            }
        });

        Column::configureUsing(function (Column $column): void {
            $column->label(fn() => static::measure('Label', function () use ($column) {
                return $this->label($column->getTable()->getModel(), $column->getName());
            }));
        });

        BaseFilter::configureUsing(function (BaseFilter $filter): void {
            $filter->label(fn() => static::measure('Label', function () use ($filter) {
                return $this->label($filter->getTable()->getModel(), $filter->getName());
            }));
        });

        InfolistComponent::configureUsing(function (InfolistComponent $infolist): void {
            $infolist->label(function () use ($infolist) {
                return $this->label($infolist->getRecord()::class, $infolist->getName());
            });
        });
    }

    protected static function loadCache(): void
    {
        static::$fs = new Filesystem();
        static::$translator = app('translator');
        $locale = static::$translator->getLocale();
        $labelCachePath = app()->bootstrapPath("cache/filament/labels-$locale.php");

        static::$cache = [];

        if ($cacheExists = static::$fs->exists($labelCachePath)) {
            if (app()->isProduction()) {
                $langLastModified = 0;
                $cacheLastModified = 1;
            } else {
                [$langLastModified, $cacheLastModified] = static::measure(
                    'Compare Label Cache',
                    function () use ($locale, $labelCachePath) {
                        try {
                            return [
                                static::$fs->lastModified(lang_path("$locale/model.php")),
                                static::$fs->lastModified($labelCachePath),
                            ];
                        } catch (Throwable) {
                            return [0, 1];
                        }
                    }
                );
            }

            if ($langLastModified < $cacheLastModified) {
                static::measure('Require Label Cache', function () use ($labelCachePath) {
                    try {
                        static::$cache = static::$fs->getRequire($labelCachePath);
                    } catch (FileNotFoundException) {
                        //
                    }
                });
            }
        }

        app()->terminating(function () use ($labelCachePath, $cacheExists) {
            if (! static::$shouldSaveCache) {
                return;
            }

            $labels = var_export(static::$cache, true);
            if (! $cacheExists && ! static::$fs->isDirectory($dir = dirname($labelCachePath))) {
                static::$fs->makeDirectory($dir);
            }

            static::$fs->put($labelCachePath, "<?php return $labels;");
        });
    }

    /** @return \App\Providers\Filament\Label\Strategy[] */
    protected static function strategies(): array
    {
        static $strategies = [
            new ModelNameStrategy(),
            new ColumnStrategy(),
            new AggregateStrategy(self::$translator),
            new RelationStrategy(),
            new RelationColumnStrategy(),
            new ModelStrategy(),
        ];

        return $strategies;
    }

    public static function label(string $model, string $column = null): string
    {
        if (! isset(static::$cache)) {
            static::loadCache();
        }

        return static::measure('Get Label', function () use ($model, $column) {
            $column ??= ' __name__ ';
            $m = basename($model);
            $start = hrtime(true);

            if (isset(static::$cache[$model][$column])) {
                try {
                    return static::$cache[$model][$column];
                } finally {
                    $end = hrtime(true);

                    static::$time["$m/$column - (cache)"] = [$start, $end];
                }
            }

            static::$shouldSaveCache = true;

            $end = function (int $start, string $class, string $note) use ($m, $column) {
                $end = hrtime(true);
                $class = basename($class);

                static::$time["$m/$column - $class ($note)"] = [$start, $end];
            };

            foreach (static::strategies() as $strategy) {
                $start = hrtime(true);
                $guess = $strategy($model, $column);

                if ($guess === null) {
                    $end($start, $strategy::class, 'null');
                    continue;
                }

                if (is_string($guess)) {
                    $end($start, $strategy::class, 'string');
                    return static::$cache[$model][$column] = $guess;
                }

                foreach ($guess->keys() as $key) {
                    $label = static::$translator->get((string) $key, $guess->replace);

                    if ($label !== $key) {
                        $end($start, $strategy::class, 'found');
                        return static::$cache[$model][$column] = $label;
                    }
                }
            }

            try {
                $start = hrtime(true);
                return static::$cache[$model][$column] = DefaultStrategy::label($column);
            } finally {
                $end($start, DefaultStrategy::class, 'found');
            }
        });
    }

    protected static function measure(string $label, Closure $closure, ?string $collector = null): mixed
    {
        static $exists = class_exists(Debugbar::class);

        if ($exists) {
            return Debugbar::measure($label, $closure, $collector);
        }

        return $closure();
    }

    protected static function addMeasure(
        string $label,
        float $start,
        float $end,
        ?array $params = [],
        ?string $collector = null
    ): void
    {
        static $exists = class_exists(Debugbar::class);

        if ($exists) {
            Debugbar::addMeasure($label, $start, $end, $params, $collector);
        }
    }

    protected static function addMessage(mixed $message, string $label = 'info'): void
    {
        static $exists = class_exists(Debugbar::class);

        if ($exists) {
            Debugbar::addMessage($message, $label);
        }
    }
}

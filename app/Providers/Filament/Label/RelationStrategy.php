<?php

namespace App\Providers\Filament\Label;

use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Error;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use ReflectionMethod;
use Throwable;

class RelationStrategy extends Strategy
{
    protected function applicable(string $model, string $column): bool | array
    {
        $relations = static::relations($model);

        if (isset($relations['columns'][$column])) {
            return [$relations['columns'][$column]];
        } elseif (isset($relations['relations'][$column])) {
            return [$relations['relations'][$column]];
        }

        return false;
    }

    protected function apply(string $model, string $column): Guess | string
    {
        return new Guess("model.$.name", $model);
    }

    public static function relations(string $model): array
    {
        static $cache = [];
        if (isset($cache[$model])) {
            return $cache[$model];
        }

        static $modelMethods = get_class_methods(Model::class);

        static $measure = class_exists(Debugbar::class)
            ? Debugbar::measure(...)
            : fn(string $label, Closure $closure) => $closure();

        return $measure('Load Label Relation', function () use (&$cache, $model, $modelMethods) {
            $cache[$model] = [
                'columns'   => [],
                'relations' => [],
            ];

            if (! class_exists($model)) {
                return $cache[$model];
            }

            /** @var Model $class */
            $class = new $model();

            if (method_exists($model, 'factory')) {
                try {
                    $factory = $model::factory();
                } catch (Error $e) {
                    if (str_contains($e->getMessage(), 'not found')) {
                        goto airi;
                    }

                    throw $e;
                }

                $class->forceFill(Arr::map($definition = $factory->definition(), fn($attribute) => match (true) {
                    $attribute instanceof Factory => null,
                    $attribute instanceof Closure => $attribute($definition),
                    default                       => $attribute,
                }));
            }

            airi:
            foreach (array_diff(get_class_methods($model), $modelMethods) as $method) {
                $ref = new ReflectionMethod($model, $method);

                if (
                    $ref->getNumberOfParameters() === 0 &&
                    is_subclass_of($ref->getReturnType()?->getName(), Relation::class)
                ) {
                    try {
                        /** @var Relation $relation */
                        $relation = $class->$method();
                    } catch (Throwable) {
                        continue;
                    }

                    $foreignKey = match (true) {
                        $relation instanceof BelongsToMany            => $relation->getForeignPivotKeyName(),
                        $relation instanceof HasManyThrough           => $relation->getFirstKeyName(),
                        method_exists($relation, 'getForeignKeyName') => $relation->getForeignKeyName(),
                        app()->hasDebugModeEnabled()                  => dd('Unknown foreign key name.', $model, $method, $relation::class),
                        default                                       => null,
                    };

                    $relationModel = $relation->getModel()::class;

                    $cache[$model]['relations'][$method] = $relationModel;

                    if ($foreignKey !== null) {
                        $cache[$model]['columns'][$foreignKey] = $relationModel;
                    }
                }
            }

            return $cache[$model];
        });
    }
}

<?php

namespace App\Providers\Filament\Label;

use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
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
            $modelMethods = array_diff(get_class_methods($model), $modelMethods);

            /** @var Model $class */
            $class = new $model();

            if (method_exists($model, 'factory')) {
                $class->forceFill(Arr::map(
                    $definition = $model::factory()->definition(),
                    function ($attribute) use ($definition) {
                        if ($attribute instanceof Factory) {
                            $attribute = null;
                        } elseif ($attribute instanceof Closure) {
                            $attribute = $attribute($definition);
                        }

                        return $attribute;
                    }
                ));
            }

            $cache[$model] = [
                'columns'   => [],
                'relations' => [],
            ];

            foreach ($modelMethods as $method) {
                $ref = new ReflectionMethod($model, $method);

                if (
                    $ref->getNumberOfParameters() === 0 &&
                    is_subclass_of($ref->getReturnType()?->getName(), Relation::class)
                ) {
                    /** @var Relation $relation */
                    try {
                        $relation = $class->$method();
                    } catch (Throwable) {
                        continue;
                    }

                    if ($relation instanceof BelongsToMany) {
                        continue;
                    } elseif ($relation instanceof HasManyThrough) {
                        $foreignKey = $relation->getFirstKeyName();
                    } elseif (method_exists($relation, 'getForeignKeyName')) {
                        $foreignKey = $relation->getForeignKeyName();
                    } else {
                        dd('Unknown foreign key name.', $model, $method, $relation::class);
                    }

                    $relationModel = $relation->getModel()::class;

                    $cache[$model]['relations'][$method] = $relationModel;
                    $cache[$model]['columns'][$foreignKey] = $relationModel;
                }
            }

            return $cache[$model];
        });
    }
}

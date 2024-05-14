<?php

namespace App\Filament;

use App\Providers\FilamentLabelServiceProvider;
use Filament\Resources\RelationManagers\RelationManager as BaseRelationManager;
use Illuminate\Database\Eloquent\Model;

class RelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return static::$title ?? FilamentLabelServiceProvider::label($ownerRecord::class, static::$relationship);
    }
}

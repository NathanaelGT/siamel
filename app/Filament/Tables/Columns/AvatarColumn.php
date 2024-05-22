<?php

namespace App\Filament\Tables\Columns;

use App\Models\Contracts\HasAccountContract;
use App\Models\User;
use Closure;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;

class AvatarColumn extends ImageColumn
{
    protected ?Closure $resolveAccountUsing = null;

    public static function make(string $name): static
    {
        return parent::make($name)
            ->defaultImageUrl(function (self $avatarColumn, $record) {
                static $provider = new UiAvatarsProvider();

                return $provider->get($avatarColumn->resolveAccount($record));
            })
            ->circular();
    }

    public function resolveAccountUsing(Closure $resolveAccountUsing): static
    {
        $this->resolveAccountUsing = $resolveAccountUsing;

        return $this;
    }

    protected function resolveAccount(Model $model): User | HasAccountContract
    {
        if ($this->resolveAccountUsing) {
            return $this->evaluate($this->resolveAccountUsing);
        }

        return $model instanceof HasAccountContract
            ? $model->account
            : $model;
    }
}

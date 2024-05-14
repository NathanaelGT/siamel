<?php

namespace App\Filament\Tables\Columns;

use App\Models\Contracts\HasAccountContract;
use App\Models\User;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Tables\Columns\ImageColumn;

class AvatarColumn extends ImageColumn
{
    public static function make(string $name): static
    {
        return parent::make($name)
            ->defaultImageUrl(function (User | HasAccountContract $record) {
                static $provider = new UiAvatarsProvider();

                $account = $record instanceof HasAccountContract
                    ? $record->account
                    : $record;

                return $provider->get($account);
            })
            ->circular();
    }
}

<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bind();
        $this->registerAssets();
        $this->setStaticProperties();
        $this->configures();

        VerifyEmail::createUrlUsing(Filament::getVerifyEmailUrl(...));
    }

    protected function bind(): void
    {
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\PasswordResetResponse::class,
            \App\Http\Responses\Auth\PasswordResetResponse::class,
        );
    }

    protected function registerAssets(): void
    {
        FilamentAsset::register([
            Css::make('filament', __DIR__ . '/../../resources/css/filament.css'),
        ]);
    }

    protected function setStaticProperties(): void
    {
        Table::$defaultCurrency = 'idr';
        Table::$defaultDateDisplayFormat = 'j M Y';
        Table::$defaultDateTimeDisplayFormat = 'j M Y \P\u\k\u\l H:i';
        Table::$defaultTimeDisplayFormat = 'H:i';
    }

    protected function configures(): void
    {
        Field::configureUsing(function (Field $field): void {
            $extraAttributes = [
                'autocomplete' => 'off',
            ];

            if (method_exists($field, 'extraInputAttributes')) {
                $field->extraInputAttributes($extraAttributes);
            } else {
                $field->extraAttributes($extraAttributes);
            }
        });
    }
}

<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Infolists\Infolist;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public const string DEFAULT_CURRENT = 'idr';
    public const string DEFAULT_DATE_DISPLAY_FORMAT = 'j M Y';
    public const string DEFAULT_DATE_TIME_DISPLAY_FORMAT = 'j M Y \P\u\k\u\l H:i';
    public const string DEFAULT_TIME_DISPLAY_FORMAT = 'H:i';

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
        Table::$defaultCurrency = static::DEFAULT_CURRENT;
        Table::$defaultDateDisplayFormat = static::DEFAULT_DATE_DISPLAY_FORMAT;
        Table::$defaultDateTimeDisplayFormat = static::DEFAULT_DATE_TIME_DISPLAY_FORMAT;
        Table::$defaultTimeDisplayFormat = static::DEFAULT_TIME_DISPLAY_FORMAT;

        Infolist::$defaultCurrency = static::DEFAULT_CURRENT;
        Infolist::$defaultDateDisplayFormat = static::DEFAULT_DATE_DISPLAY_FORMAT;
        Infolist::$defaultDateTimeDisplayFormat = static::DEFAULT_DATE_TIME_DISPLAY_FORMAT;
        Infolist::$defaultTimeDisplayFormat = static::DEFAULT_TIME_DISPLAY_FORMAT;
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

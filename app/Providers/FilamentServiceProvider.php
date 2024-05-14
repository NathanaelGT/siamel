<?php

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerAssets();
        $this->setStaticProperties();
        $this->configures();
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

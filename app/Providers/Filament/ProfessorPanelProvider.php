<?php

namespace App\Providers\Filament;

use App\Filament\FilamentPanel;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;

class ProfessorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return FilamentPanel::default($panel->id('professor'))
            ->path('dosen')
            ->topNavigation()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
            ]);
    }
}

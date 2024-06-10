<?php

namespace App\Filament;

use App\Http\Middleware\Authenticate;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\RequestPasswordReset;
use App\Livewire\Pages\Auth\ResetPassword;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

abstract class FilamentPanel
{
    public static function default(Panel $panel): Panel
    {
        $name = Str::studly($panel->getId());

        return $panel
            ->spa()
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->discoverPages(in: app_path("Filament/$name/Pages"), for: "App\\Filament\\$name\\Pages")
            ->discoverWidgets(in: app_path("Filament/$name/Widgets"), for: "App\\Filament\\$name\\Widgets")
            ->discoverClusters(in: app_path("Filament/$name/Clusters"), for: "App\\Filament\\$name\\Clusters")
            ->discoverResources(in: app_path("Filament/$name/Resources"), for: "App\\Filament\\$name\\Resources")
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(hasAvatars: true, slug: 'profil'),
            ]);
    }
}

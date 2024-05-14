<?php

use App\Livewire\Pages\Auth\AcceptInvitation;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\RequestPasswordReset;
use App\Livewire\Pages\Auth\ResetPassword;
use Illuminate\Support\Facades\Route;

Route::get('/daftar/{userId}/{hash}', AcceptInvitation::class)
    ->middleware('signed')
    ->name('accept-invitation');

Route::get('/login', Login::class)->name('login');

Route::prefix('reset-password')->group(function () {
    Route::get('/', RequestPasswordReset::class)->name('password.request');
    Route::get('/{email}/{token}', ResetPassword::class)
        ->middleware('signed')
        ->name('password.reset');
});

Route::redirect('/', '/login');

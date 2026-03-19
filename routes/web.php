<?php

use Illuminate\Support\Facades\Route;

Route::permanentRedirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::name('memo.')->group(function () {
        Route::livewire('/memo/simple', 'pages::memo.simple')->name('simple');
        Route::livewire('/memo/normal', 'pages::memo.normal')->name('normal');
    });
});

require __DIR__.'/settings.php';

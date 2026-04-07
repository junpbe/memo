<?php

use Illuminate\Support\Facades\Route;

Route::permanentRedirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('memo')->name('memo.')->group(function () {
        Route::livewire('/simple', 'pages::memo.simple')->name('simple');
        Route::livewire('/normal', 'pages::memo.normal')->name('normal');
    });
    Route::prefix('tag')->name('tag.')->group(function () {
        Route::livewire('/management', 'pages::tag.management')->name('management');
    });
});

require __DIR__.'/settings.php';

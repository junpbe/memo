<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// })->name('home');
Route::permanentRedirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('/simple', 'pages::simple')->name('simple');
    Route::livewire('/normal', 'pages::normal')->name('normal');
});

require __DIR__.'/settings.php';

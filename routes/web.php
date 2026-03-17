<?php

use App\Http\Controllers\PrayerTimesWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/prayer-times', 301)->name('home');

Route::get('/prayer-times', [PrayerTimesWebController::class, 'index'])
    ->name('prayer-times.index');

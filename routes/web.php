<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'prayerTimes']);
Route::get('/prayer-times', [HomeController::class, 'prayerTimes']);

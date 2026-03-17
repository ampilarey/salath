<?php

use App\Http\Controllers\Api\IslandsController;
use App\Http\Controllers\Api\NearestIslandController;
use App\Http\Controllers\Api\PrayerTimesApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')
    ->prefix('prayer-times')
    ->name('api.prayer-times.')
    ->group(function () {
        Route::get('islands',  [IslandsController::class, 'index'])->name('islands');
        Route::get('nearest',  NearestIslandController::class)->name('nearest');
        Route::get('',         PrayerTimesApiController::class)->name('show');
    });

<?php

use App\Http\Controllers\Api\PrayerTimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('prayer-times')->group(function () {
    Route::get('islands',  [PrayerTimeController::class, 'islands']);
    Route::get('nearest',  [PrayerTimeController::class, 'nearest']);
    Route::get('',         [PrayerTimeController::class, 'times']);
});

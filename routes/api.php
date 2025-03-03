<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/verify-license', [LicenseController::class, 'verifyLicense']);
Route::get('/image/{image}', [LicenseController::class, 'getImage'])->name('image');

Route::middleware(['check.license'])->group(function () {
    Route::post('/get-content', [LicenseController::class, 'getContent']);

    Route::post('/log-usage', [LicenseController::class, 'logUsage']);
});

Route::post('/test-api', [LicenseController::class, 'testApi']);
Route::post('/license-stats', [LicenseController::class, 'getLicenseStats']);

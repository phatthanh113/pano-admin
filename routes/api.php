<?php

use App\Http\Controllers\Api\ProjectApiController;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Pano Frontend
|--------------------------------------------------------------------------
| Tất cả routes prefix /api được serve cùng origin với Laravel.
| Khi deploy production, frontend build sẽ nằm trong public/ và gọi /api/* same-origin => không CORS.
| Khi dev, Vite proxy /api -> http://pano-admin.test => cũng không CORS.
*/

Route::get('/health', [ProjectApiController::class, 'health']);
Route::get('/projects', [ProjectApiController::class, 'index']);
Route::get('/projects/{slug}', [ProjectApiController::class, 'show']);
Route::get('/site-settings', function () {
    $s = SiteSetting::current();
    return response()->json([
        'company_name' => $s->company_name,
        'logo' => $s->logo,
        'logo_url' => $s->logo_url,
        'phone' => $s->phone,
        'email' => $s->email,
        'address' => $s->address,
        'website' => $s->website,
        'description' => $s->description,
        'facebook' => $s->facebook,
        'copyright' => $s->copyright,
    ]);
});

// fallback 404 json
Route::fallback(function () {
    return response()->json(['message' => 'API endpoint not found'], 404);
});

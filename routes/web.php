<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Serve Pano Frontend (Same-Origin, No CORS)
|--------------------------------------------------------------------------
| - /admin/* : Filament admin (giữ nguyên)
| - /api/*   : API (đã có routes/api.php)
| - /storage/* : file upload
| - /up      : health check
| - /*       : còn lại serve React SPA từ public/pano (nếu đã deploy build)
|             Khi chưa build, fallback về welcome view để dev không lỗi
*/

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'vi', 'ja'], true)) {
        abort(400, 'Unsupported locale');
    }
    session(['locale' => $locale]);
    return redirect()->back(fallback: '/')->withCookie(cookie()->forever('locale', $locale));
})->name('lang.switch');

// Auth cho frontend pano (same-origin session, không cần sanctum token)
Route::post('/api/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->name('api.auth.login');
Route::post('/api/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth')->name('api.auth.logout');
Route::get('/api/auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth')->name('api.auth.me');

Route::get('/', function () {
    $panoIndex = public_path('pano/index.html');
    if (file_exists($panoIndex)) {
        return response()->file($panoIndex);
    }
    return view('welcome');
});

// SPA fallback: mọi route không phải api/admin/storage/up và không phải file .php sẽ trả về pano/index.html
// để React Router hoạt động, và đảm bảo frontend + backend cùng origin
// Loại trừ *.php để seed_*.php, migrate.php, hello.php... được Apache serve trực tiếp, không bị SPA nuốt
Route::get('/{any}', function () {
    $panoIndex = public_path('pano/index.html');
    if (file_exists($panoIndex)) {
        return response()->file($panoIndex);
    }
    abort(404);
})->where('any', '^(?!api|admin|storage|up|.*\.php$).*$');

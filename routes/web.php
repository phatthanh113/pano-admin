<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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

$migrateHandler = function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    $secret = env('MIGRATE_SECRET', 'pano-migrate-2026');
    if (!auth()->check() && $token !== $secret) {
        abort(403, 'Vui lòng đăng nhập admin hoặc thêm ?token='.$secret);
    }
    $output = '';
    try {
        Artisan::call('migrate', ['--force' => true]);
        $output .= Artisan::output();
    } catch (\Throwable $e) {
        $output .= 'Artisan migrate error: '.$e->getMessage()."\n";
    }
    try {
        if (!Schema::hasColumn('panoramas', 'extra_images')) {
            Schema::table('panoramas', function ($table) {
                $table->json('extra_images')->nullable()->after('url');
            });
            $output .= "\n[Fallback] Added extra_images JSON column via Schema";
        } else {
            $output .= "\n[Check] extra_images đã tồn tại";
        }
    } catch (\Throwable $e) {
        try {
            if (!Schema::hasColumn('panoramas', 'extra_images')) {
                Schema::table('panoramas', function ($table) {
                    $table->text('extra_images')->nullable()->after('url');
                });
                $output .= "\n[Fallback] Added extra_images TEXT column via Schema";
            }
        } catch (\Throwable $e2) {
            $output .= "\n[Fallback TEXT error] ".$e2->getMessage();
            $output .= "\n[Original error] ".$e->getMessage();
        }
    }
    try {
        Artisan::call('optimize:clear');
        $output .= "\n".Artisan::output();
    } catch (\Throwable $e) {}
    return response()->json(['success' => true, 'output' => $output]);
};
Route::get('/run-migrate', $migrateHandler)->name('migrate.run');
Route::get('/admin/run-migrate', $migrateHandler);

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

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
        $link = public_path('storage');
        $needsLink = false;
        if (is_link($link)) {
            $target = @readlink($link);
            $output .= "\n[storage:link] existing target: ".$target;
            if (!$target || !is_dir($target) || !file_exists($link.'/panoramas/extra/01M1VCSM759DM7PHZMPJY8Q158.jpg')) {
                @unlink($link);
                $output .= "\n[storage:link] removed broken link";
                $needsLink = true;
            } else {
                $output .= "\n[storage:link] OK";
            }
        } elseif (!file_exists($link)) {
            $needsLink = true;
        } else {
            $output .= "\n[storage:link] exists as file/dir not link";
            @unlink($link);
            $needsLink = true;
        }
        if ($needsLink) {
            Artisan::call('storage:link');
            $output .= "\n[storage:link] recreated: ".Artisan::output();
        }
        // đảm bảo folder panoramas/extra tồn tại
        $extraPath = storage_path('app/public/panoramas/extra');
        if (!is_dir($extraPath)) {
            @mkdir($extraPath, 0755, true);
            $output .= "\n[mkdir] created ".$extraPath;
        }
        $output .= "\n[extra dir exists] ".(is_dir($extraPath) ? 'yes' : 'no');
        $output .= " writable: ".(is_writable($extraPath) ? 'yes' : 'no');
        $output .= " files: ".json_encode(array_slice(\Illuminate\Support\Facades\Storage::disk('public')->files('panoramas/extra'), 0, 3));
        $output .= " public_exists check: ".(file_exists(public_path('storage/panoramas/extra/01M1VCSM759DM7PHZMPJY8Q158.jpg')) ? 'yes' : 'no');
    } catch (\Throwable $e) {
        $output .= "\n[storage:link error] ".$e->getMessage();
    }
    try {
        Artisan::call('optimize:clear');
        $output .= "\n".Artisan::output();
    } catch (\Throwable $e) {}
    return response()->json(['success' => true, 'output' => $output]);
};
Route::get('/run-migrate', $migrateHandler)->name('migrate.run');
Route::get('/admin/run-migrate', $migrateHandler);

Route::get('/test-extra-save', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    $secret = env('MIGRATE_SECRET', 'pano-migrate-2026');
    if (!auth()->check() && $token !== $secret) {
        abort(403, 'Thêm ?token='.$secret.' hoặc đăng nhập admin');
    }
    try {
        $p = \App\Models\Panorama::first();
        if (!$p) return response()->json(['error' => 'No panorama found'], 404);
        $old = $p->extra_images;
        // thử set và save
        $p->extra_images = ['panoramas/extra/test-diag.jpg'];
        $p->save();
        $check = \App\Models\Panorama::find($p->id)->extra_images;
        // restore
        $p->extra_images = $old;
        $p->save();
        $storageOk = \Illuminate\Support\Facades\Storage::disk('public')->exists('panoramas/extra') || @mkdir(storage_path('app/public/panoramas/extra'), 0755, true);
        $writable = is_writable(storage_path('app/public/panoramas/extra')) ? 'yes' : 'no';
        return response()->json(['success' => true, 'id' => $p->id, 'old' => $old, 'after_test' => $check, 'storage_writable' => $writable, 'hasColumn' => \Illuminate\Support\Facades\Schema::hasColumn('panoramas', 'extra_images')]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(), 0, 2000)], 500);
    }
});

Route::get('/list-extra', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    $secret = env('MIGRATE_SECRET', 'pano-migrate-2026');
    if (!auth()->check() && $token !== $secret) {
        abort(403, 'Thêm ?token='.$secret);
    }
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $files = $disk->files('panoramas/extra');
    $details = [];
    foreach ($files as $f) {
        $full = storage_path('app/public/'.$f);
        $details[] = [
            'file' => $f,
            'exists' => file_exists($full) ? 'yes' : 'no',
            'size' => file_exists($full) ? filesize($full) : null,
            'size_kb' => file_exists($full) ? round(filesize($full)/1024) : null,
            'url' => $disk->url($f),
            'public_exists' => file_exists(public_path('storage/'.$f)) ? 'yes' : 'no',
        ];
    }
    $extraPath = storage_path('app/public/panoramas/extra');
    $extraExists = is_dir($extraPath) ? 'yes' : 'no';
    $extraWritable = is_writable($extraPath) ? 'yes' : (is_dir(dirname($extraPath)) && is_writable(dirname($extraPath)) ? 'parent writable' : 'no');
    $tmpPath = storage_path('app/livewire-tmp');
    $tmpExists = is_dir($tmpPath) ? 'yes' : 'no';
    $tmpWritable = is_writable($tmpPath) ? 'yes' : (is_dir(dirname($tmpPath)) && is_writable(dirname($tmpPath)) ? 'parent writable' : 'no');
    // framework livewire tmp alternative
    $tmpPath2 = storage_path('framework/livewire-tmp');
    $tmp2Exists = is_dir($tmpPath2) ? 'yes' : 'no';
    return response()->json(['files' => $details, 'extra_dir' => $extraExists, 'extra_writable' => $extraWritable, 'livewire_tmp' => $tmpExists, 'livewire_writable' => $tmpWritable, 'livewire_tmp2' => $tmp2Exists, 'check_file' => $request->query('file') ? ['exists' => $disk->exists($request->query('file')), 'size' => $disk->exists($request->query('file')) ? $disk->size($request->query('file')) : null] : null]);
});

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

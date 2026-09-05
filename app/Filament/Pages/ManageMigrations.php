<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class ManageMigrations extends Page
{
    protected string $view = 'filament.pages.manage-migrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCommandLine;

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?string $navigationLabel = 'Migrate';

    protected static ?int $navigationSort = 100;

    public ?string $output = null;
    public ?string $statusOutput = null;

    public static function canAccess(): bool
    {
        // chỉ cho user đã đăng nhập admin, có thể thêm check role nếu cần
        return auth()->check();
    }

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        try {
            Artisan::call('migrate:status');
            $this->statusOutput = Artisan::output();
        } catch (\Throwable $e) {
            $this->statusOutput = 'Lỗi lấy status: '.$e->getMessage();
        }
        // check extra_images nhanh
        try {
            $has = Schema::hasColumn('panoramas', 'extra_images') ? 'Đã có' : 'Chưa có';
            $this->statusOutput .= "\n\n[Check] panoramas.extra_images: ".$has;
        } catch (\Throwable $e) {}
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('migrate')
                ->label('Chạy Migrate')
                ->icon(Heroicon::OutlinedPlay)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Chạy migrate?')
                ->modalDescription('Sẽ chạy php artisan migrate --force và optimize:clear')
                ->action(function () {
                    $out = '';
                    try {
                        Artisan::call('migrate', ['--force' => true]);
                        $out .= Artisan::output();
                    } catch (\Throwable $e) {
                        $out .= 'Error: '.$e->getMessage()."\n";
                    }
                    // fallback extra_images
                    try {
                        if (!Schema::hasColumn('panoramas', 'extra_images')) {
                            Schema::table('panoramas', function ($table) {
                                $table->json('extra_images')->nullable()->after('url');
                            });
                            $out .= "\n[Fallback] Added extra_images JSON";
                        }
                    } catch (\Throwable $e) {
                        try {
                            if (!Schema::hasColumn('panoramas', 'extra_images')) {
                                Schema::table('panoramas', function ($table) {
                                    $table->text('extra_images')->nullable()->after('url');
                                });
                                $out .= "\n[Fallback] Added extra_images TEXT";
                            }
                        } catch (\Throwable $e2) {
                            $out .= "\nFallback error: ".$e2->getMessage();
                        }
                    }
                    try {
                        Artisan::call('optimize:clear');
                        $out .= "\n".Artisan::output();
                    } catch (\Throwable $e) {}
                    $this->output = $out;
                    $this->refreshStatus();
                    Notification::make()->title('Đã chạy migrate')->body(mb_substr($out, 0, 300))->success()->send();
                }),
            Action::make('refresh')
                ->label('Làm mới')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(fn () => $this->refreshStatus()),
            Action::make('optimizeClear')
                ->label('Clear Cache')
                ->icon(Heroicon::OutlinedTrash)
                ->color('gray')
                ->action(function () {
                    Artisan::call('optimize:clear');
                    $this->output = Artisan::output();
                    Notification::make()->title('Đã clear cache')->success()->send();
                }),
        ];
    }
}

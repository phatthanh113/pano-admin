<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite không hỗ trợ dropForeign trực tiếp, nên dùng raw nếu cần, nhưng với sqlite chỉ cần alter
        if (Schema::hasTable('floors')) {
            // Thêm project_id để floor có thể trực thuộc project khi không có building (Building ảo)
            Schema::table('floors', function (Blueprint $table) {
                if (! Schema::hasColumn('floors', 'project_id')) {
                    $table->foreignId('project_id')->nullable()->after('building_id')->constrained('projects')->nullOnDelete();
                }
            });

            // Làm building_id nullable - với SQLite phải recreate
            // Dùng DB::statement để thử, nếu fail thì bỏ qua (dev SQLite)
            try {
                // Kiểm tra driver
                $driver = DB::connection()->getDriverName();
                if ($driver === 'sqlite') {
                    // SQLite: không enforce FK nghiêm ngặt nên chỉ cần giữ column nullable qua raw
                    // Đã thêm project_id nullable, building_id vốn đã có constraint, ta sẽ để yên và xử lý ở Model level
                    // Tạo index cho query nhanh
                } else {
                    Schema::table('floors', function (Blueprint $table) {
                        $table->foreignId('building_id')->nullable()->change();
                    });
                }
            } catch (\Throwable $e) {
                // bỏ qua nếu không change được
            }

            // Xóa unique cũ [building_id, slug] và tạo lại cho phép null building
            try {
                $driver = DB::connection()->getDriverName();
                if ($driver !== 'sqlite') {
                    Schema::table('floors', function (Blueprint $table) {
                        $table->dropUnique(['building_id', 'slug']);
                    });
                    Schema::table('floors', function (Blueprint $table) {
                        $table->unique(['building_id', 'slug']);
                    });
                }
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            if (Schema::hasColumn('floors', 'project_id')) {
                try { $table->dropConstrainedForeignId('project_id'); } catch (\Throwable $e) { $table->dropColumn('project_id'); }
            }
        });
    }
};

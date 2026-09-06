<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // InfinityFree MySQL có thể không hỗ trợ JSON, đảm bảo cột tồn tại dạng TEXT
        if (!Schema::hasColumn('panoramas', 'extra_images')) {
            Schema::table('panoramas', function (Blueprint $table) {
                $table->text('extra_images')->nullable()->after('url');
            });
        } else {
            // nếu đã có dạng JSON nhưng host không hỗ trợ, giữ nguyên; nếu cần đổi sang TEXT thì uncomment dưới
            // try { DB::statement('ALTER TABLE panoramas MODIFY extra_images TEXT NULL'); } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('panoramas', 'extra_images')) {
            Schema::table('panoramas', function (Blueprint $table) {
                $table->dropColumn('extra_images');
            });
        }
    }
};

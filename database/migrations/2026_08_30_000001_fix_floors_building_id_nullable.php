<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            // MySQL/Postgres có thể change trực tiếp
            try {
                Schema::table('floors', function ($table) {
                    $table->foreignId('building_id')->nullable()->change();
                });
            } catch (\Throwable $e) {}
            return;
        }

        // SQLite: phải recreate table
        DB::statement('PRAGMA foreign_keys = OFF');

        // Kiểm tra column đã nullable chưa
        $cols = DB::select("PRAGMA table_info('floors')");
        $buildingCol = collect($cols)->firstWhere('name', 'building_id');
        // notnull = 0 là nullable, 1 là NOT NULL
        if ($buildingCol && (int)$buildingCol->notnull === 0) {
            DB::statement('PRAGMA foreign_keys = ON');
            return; // đã nullable rồi
        }

        // Tạo bảng mới với building_id nullable
        Schema::create('floors_new', function ($table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('building_id')->nullable()->constrained('buildings')->nullOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('short_label')->nullable();
            $table->text('description')->nullable();
            $table->string('plan_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Copy data
        $hasProjectId = collect($cols)->contains(fn($c) => $c->name === 'project_id');
        if ($hasProjectId) {
            DB::statement('INSERT INTO floors_new (id, project_id, building_id, slug, name, short_label, description, plan_image, sort_order, is_active, created_at, updated_at) SELECT id, project_id, building_id, slug, name, short_label, description, plan_image, sort_order, is_active, created_at, updated_at FROM floors');
        } else {
            DB::statement('INSERT INTO floors_new (id, building_id, slug, name, short_label, description, plan_image, sort_order, is_active, created_at, updated_at) SELECT id, building_id, slug, name, short_label, description, plan_image, sort_order, is_active, created_at, updated_at FROM floors');
        }

        Schema::dropIfExists('floors');
        Schema::rename('floors_new', 'floors');

        // Tạo lại unique index như cũ (building_id + slug) - SQLite cho phép null nên không bắt buộc
        try {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS floors_building_id_slug_unique ON floors (building_id, slug)');
        } catch (\Throwable $e) {}

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // không revert
    }
};

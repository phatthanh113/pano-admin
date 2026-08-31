<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panoramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('number')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('url');
            $table->string('label')->nullable();
            $table->decimal('map_x', 6, 2)->nullable();
            $table->decimal('map_y', 6, 2)->nullable();
            $table->decimal('map_angle', 6, 2)->nullable();
            $table->decimal('yaw', 8, 2)->default(0);
            $table->decimal('pitch', 8, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['building_id', 'floor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panoramas');
    }
};

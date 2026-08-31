<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panorama_id')->constrained('panoramas')->cascadeOnDelete();
            $table->foreignId('target_panorama_id')->nullable()->constrained('panoramas')->nullOnDelete();
            $table->string('tooltip')->nullable();
            $table->decimal('yaw', 8, 2)->default(0);
            $table->decimal('pitch', 8, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};

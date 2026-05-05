<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            // Speeds up JOIN queries filtering by make_id + is_active
            $table->index(['make_id', 'is_active'], 'car_models_make_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->dropIndex('car_models_make_active_idx');
        });
    }
};

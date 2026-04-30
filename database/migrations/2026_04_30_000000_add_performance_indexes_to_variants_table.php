<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            // Speeds up: SELECT DISTINCT year FROM variants WHERE is_active = true
            $table->index(['is_active', 'year'], 'variants_active_year_idx');

            // Speeds up the whereHas subquery used by /makes and /models endpoints:
            // WHERE year = ? AND is_active = true AND model_id = ?
            $table->index(['year', 'is_active', 'model_id'], 'variants_year_active_model_idx');
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropIndex('variants_active_year_idx');
            $table->dropIndex('variants_year_active_model_idx');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            // JSONB column for flexible, indexable additional specs
            // e.g. {"horsepower": 180, "seats": 5, "fuel_type": "petrol", "color_options": [...]}
            // Supplements the typed columns (engine, transmission) rather than replacing them.
            $table->jsonb('specs')->nullable()->after('gcc_specs');
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn('specs');
        });
    }
};

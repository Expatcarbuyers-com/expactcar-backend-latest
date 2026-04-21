<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tracks which legacy MySQL booking row this came from.
            // NULL means the booking came from the new system.
            // UNIQUE index makes the ETL command idempotent — re-running
            // it will never create duplicate bookings.
            $table->unsignedBigInteger('legacy_source_id')->nullable()->after('user_agent');
            $table->unique('legacy_source_id');

            // Where the booking originated: 'web_form' | 'legacy_etl' | etc.
            $table->string('source', 30)->nullable()->after('legacy_source_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['legacy_source_id']);
            $table->dropColumn(['legacy_source_id', 'source']);
        });
    }
};

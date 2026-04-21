<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_bookings', function (Blueprint $table) {
            $table->id();

            // The original MySQL primary key — used to make the ETL command idempotent
            $table->unsignedInteger('legacy_source_id')->unique();

            // If ETL matched the legacy brand/model text to a real variant, link it
            $table->foreignId('matched_variant_id')
                ->nullable()
                ->constrained('variants')
                ->nullOnDelete();

            // Full raw row from MySQL stored as JSONB for complete auditability
            $table->jsonb('raw_data');

            // Any notes the ETL command adds about why a match failed
            $table->text('etl_notes')->nullable();

            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();

            $table->index('matched_variant_id');
            $table->index('imported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_bookings');
    }
};

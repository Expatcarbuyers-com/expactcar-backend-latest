<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Rename notes → internal_notes for semantic clarity
            $table->renameColumn('notes', 'internal_notes');

            // Assign leads to agents
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            // Drop Zoho field — Filament is the source of truth now
            $table->dropIndex('bookings_zoho_lead_id_index');
            $table->dropColumn('zoho_lead_id');
        });

        // Partial unique index for deduplication:
        // A phone+variant combo can only exist once.
        // NULL variant_id is excluded so archived/deleted-variant bookings
        // don't block new submissions.
        DB::statement('
            CREATE UNIQUE INDEX bookings_phone_variant_uq
            ON bookings (phone, variant_id)
            WHERE variant_id IS NOT NULL
        ');

        // Enforce valid status values at the DB level
        DB::statement("
            ALTER TABLE bookings
            ADD CONSTRAINT bookings_status_check
            CHECK (status IN (
                'pending',
                'contacted',
                'appraised',
                'offer_made',
                'closed_won',
                'closed_lost'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS bookings_phone_variant_uq');
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');

        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('internal_notes', 'notes');
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
            $table->string('zoho_lead_id', 50)->nullable();
            $table->index('zoho_lead_id');
        });
    }
};

<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CarModel;
use App\Models\Make;
use App\Models\Variant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EtlLegacyBookings extends Command
{
    protected $signature = 'etl:legacy-bookings
        {--dry-run : Preview what would be imported without writing anything}
        {--chunk=200 : Number of legacy rows to process per batch}';

    protected $description = 'Import legacy MySQL bookings into Postgres. Idempotent — safe to re-run.';

    private int $matched   = 0;
    private int $unmatched = 0;
    private int $skipped   = 0;
    private int $errors    = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $chunk  = (int) $this->option('chunk');

        if ($dryRun) {
            $this->warn('DRY-RUN mode — nothing will be written to the database.');
        }

        $this->info('Connecting to legacy MySQL database...');

        try {
            $total = DB::connection('mysql_legacy')->table('bookings')->count();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to legacy database: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Found {$total} rows in legacy `bookings` table.");

        if ($total === 0) {
            $this->warn('Nothing to import.');
            return self::SUCCESS;
        }

        // Pre-load make/model slugs into memory to avoid N+1 hits.
        $makeMap = Make::pluck('id', 'slug');   // slug → id

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // JOIN bookings → cars → brands + car_models to resolve make/model/year.
        DB::connection('mysql_legacy')
            ->table('bookings as b')
            ->select([
                'b.id',
                'b.name',
                'b.email',
                'b.phone',
                'b.utm_source',
                'b.utm_medium',
                'b.utm_campaign',
                'b.user_ip',
                'b.status as legacy_status',
                'b.created_at',
                'b.updated_at',
                'c.year',
                'br.name as brand_name',
                'br.slug as brand_slug',
                'cm.name as model_name_raw',
                'cm.slug as model_slug',
            ])
            ->leftJoin('cars as c', 'c.id', '=', 'b.car_id')
            ->leftJoin('brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoin('car_models as cm', 'cm.id', '=', 'c.car_model_id')
            ->orderBy('b.id')
            ->chunk($chunk, function ($rows) use ($dryRun, $makeMap, $bar) {
                foreach ($rows as $row) {
                    $bar->advance();
                    $this->processRow((array) $row, $dryRun, $makeMap);
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Matched', 'Unmatched', 'Skipped (duplicate)', 'Errors'],
            [[$this->matched, $this->unmatched, $this->skipped, $this->errors]],
        );

        return self::SUCCESS;
    }

    private function processRow(array $row, bool $dryRun, $makeMap): void
    {
        try {
            // ── Idempotency check ────────────────────────────────────
            if (Booking::where('legacy_source_id', $row['id'])->exists()) {
                $this->skipped++;
                return;
            }

            // ── Variant matching ─────────────────────────────────────
            $variantId = null;
            $year      = (int) ($row['year'] ?? 0);
            $brandSlug = (string) ($row['brand_slug'] ?? Str::slug((string) ($row['brand_name'] ?? '')));
            $modelSlug = (string) ($row['model_slug'] ?? Str::slug((string) ($row['model_name_raw'] ?? '')));

            if ($brandSlug && $modelSlug && isset($makeMap[$brandSlug])) {
                $makeId   = $makeMap[$brandSlug];
                $carModel = CarModel::where('make_id', $makeId)
                    ->where('slug', $modelSlug)
                    ->first();

                if ($carModel) {
                    // Prefer exact year match; fall back to any active variant.
                    $variant = Variant::where('model_id', $carModel->id)
                        ->where('is_active', true)
                        ->when($year, fn ($q) => $q->where('year', $year))
                        ->first()
                        ?? Variant::where('model_id', $carModel->id)
                            ->where('is_active', true)
                            ->first();

                    $variantId = $variant?->id;
                }
            }

            // ── Build utm_data JSON ──────────────────────────────────
            $utmData = array_filter([
                'utm_source'   => $row['utm_source']   ?? null,
                'utm_medium'   => $row['utm_medium']   ?? null,
                'utm_campaign' => $row['utm_campaign'] ?? null,
            ]) ?: null;

            // ── Booking payload ──────────────────────────────────────
            $bookingData = [
                'variant_id'       => $variantId,
                'make_name'        => (string) ($row['brand_name'] ?? ''),
                'model_name'       => (string) ($row['model_name_raw'] ?? ''),
                'variant_name'     => '',
                'year'             => $year ?: 0,
                'mileage'          => 0,
                'name'             => (string) ($row['name'] ?? ''),
                'phone'            => (string) ($row['phone'] ?? ''),
                'email'            => (string) ($row['email'] ?? ''),
                'utm_data'         => $utmData,
                'status'           => $this->mapStatus((string) ($row['legacy_status'] ?? '')),
                'ip_address'       => $this->sanitizeIp($row['user_ip'] ?? null),
                'legacy_source_id' => $row['id'],
                'source'           => 'legacy_etl',
                'created_at'       => $row['created_at'] ?? now(),
                'updated_at'       => $row['updated_at'] ?? now(),
            ];

            if ($dryRun) {
                $variantId ? $this->matched++ : $this->unmatched++;
                return;
            }

            // ── Write booking ────────────────────────────────────────
            Booking::create($bookingData);

            if (! $variantId) {
                // Store unmatched row for manual review.
                DB::table('legacy_bookings')->insert([
                    'legacy_source_id'   => $row['id'],
                    'matched_variant_id' => null,
                    'raw_data'           => json_encode($row),
                    'etl_notes'          => "No variant match: brand='{$row['brand_name']}' model='{$row['model_name_raw']}' year='{$row['year']}'",
                    'imported_at'        => now(),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $this->unmatched++;
            } else {
                $this->matched++;
            }
        } catch (\Throwable $e) {
            $this->errors++;
            $this->newLine();
            $this->error("Row id={$row['id']}: " . $e->getMessage());
        }
    }

    /**
     * Map legacy status strings to the new constrained enum values.
     * Unknown values default to 'pending'.
     */
    private function mapStatus(string $legacyStatus): string
    {
        return match (strtolower(trim($legacyStatus))) {
            'confirmed', 'approved', 'contacted' => 'contacted',
            'completed', 'inspected'             => 'appraised',
            'offer', 'offer_made'                => 'offer_made',
            'won', 'sold', 'closed_won'          => 'closed_won',
            'lost', 'cancelled', 'closed_lost'   => 'closed_lost',
            default                              => 'pending',
        };
    }

    /**
     * Validate IP address — the legacy column is a plain varchar.
     * Return null when it can't be parsed.
     */
    private function sanitizeIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }
        return filter_var(trim($ip), FILTER_VALIDATE_IP) !== false ? trim($ip) : null;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\Make;
use App\Models\Variant;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CarController extends Controller
{
    // Cache TTLs (seconds)
    private const TTL_CATALOG = 86400; // 24 hours
    private const TTL_SLUG    = 3600;  // 1 hour

    /**
     * Returns the catalog cache version key.
     * Incrementing this value effectively invalidates all catalog caches
     * without needing Redis tags (compatible with database cache driver).
     */
    private function catalogVersion(): int
    {
        return (int) Cache::get('catalog_version', 1);
    }

    private function key(string $name): string
    {
        return "catalog:v{$this->catalogVersion()}:{$name}";
    }

    private function catalogJson(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data])
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400');
    }

    private function makesForYear(?string $year): \Illuminate\Support\Collection
    {
        $query = Make::active()->orderBy('display_order')->orderBy('name');

        if ($year) {
            $query->whereHas('models.variants', fn ($q) =>
                $q->where('year', $year)->where('is_active', true)
            );
        }

        return $query->get(['id', 'name', 'slug', 'logo_url']);
    }

    // ── GET /api/v1/years ────────────────────────────────────

    public function years(): JsonResponse
    {
        $data = Cache::remember($this->key('years'), self::TTL_CATALOG, function () {
            return Variant::where('is_active', true)
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year');
        });

        return $this->catalogJson($data);
    }

    // ── GET /api/v1/preload ───────────────────────────────────
    // Returns years + makes for the latest year in one request,
    // eliminating the waterfall of two sequential fetches on form mount.

    public function preload(): JsonResponse
    {
        $data = Cache::remember($this->key('preload'), self::TTL_CATALOG, function () {
            $years = Variant::where('is_active', true)
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year');

            $latestYear = $years->first() ? (string) $years->first() : null;

            return [
                'years'       => $years,
                'latest_year' => $latestYear,
                'makes'       => $this->makesForYear($latestYear),
            ];
        });

        return $this->catalogJson($data);
    }

    // ── GET /api/v1/makes?year= ──────────────────────────────

    public function makes(Request $request): JsonResponse
    {
        $year = $request->query('year');
        $cacheKey = $this->key('makes:' . ($year ?? 'all'));

        $data = Cache::remember($cacheKey, self::TTL_CATALOG, function () use ($year) {
            return $this->makesForYear($year);
        });

        return $this->catalogJson($data);
    }

    // ── GET /api/v1/models?make_id=&year= ───────────────────

    public function models(Request $request): JsonResponse
    {
        $request->validate([
            'make_id' => 'required|integer|exists:makes,id',
            'year'    => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
        ]);

        $makeId = $request->query('make_id');
        $year   = $request->query('year');
        $cacheKey = $this->key("models:{$makeId}:" . ($year ?? 'all'));

        $data = Cache::remember($cacheKey, self::TTL_CATALOG, function () use ($makeId, $year) {
            $query = CarModel::active()
                ->where('make_id', $makeId)
                ->orderBy('name');

            if ($year) {
                $query->whereHas('variants', fn ($q) =>
                    $q->where('year', $year)->where('is_active', true)
                );
            }

            return $query->get(['id', 'name', 'slug']);
        });

        return $this->catalogJson($data);
    }

    // ── GET /api/v1/variants?model_id=&year= ────────────────

    public function variants(Request $request): JsonResponse
    {
        $request->validate([
            'model_id' => 'required|integer|exists:car_models,id',
            'year'     => 'required|integer|min:1980|max:' . (date('Y') + 1),
        ]);

        $modelId  = $request->query('model_id');
        $year     = $request->query('year');
        $cacheKey = $this->key("variants:{$modelId}:{$year}");

        $data = Cache::remember($cacheKey, self::TTL_CATALOG, function () use ($modelId, $year) {
            return Variant::where('model_id', $modelId)
                ->where('year', $year)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'body_type', 'engine', 'transmission', 'gcc_specs']);
        });

        return $this->catalogJson($data);
    }

    // ── GET /api/v1/search?q= ───────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $variants = Variant::search($q)->take(10)->get();
            $variants->loadMissing('model.make');
            $source = 'meilisearch';
        } catch (\Exception) {
            // Meilisearch unavailable — fall back to Postgres ILIKE
            $variants = Variant::with('model.make')
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    if (is_numeric($q) && strlen($q) === 4) {
                        $query->where('year', (int) $q);
                    } else {
                        $query->where('name', 'ilike', "%{$q}%")
                            ->orWhereHas('model', fn ($q2) =>
                                $q2->where('name', 'ilike', "%{$q}%")
                                   ->orWhereHas('make', fn ($q3) =>
                                       $q3->where('name', 'ilike', "%{$q}%")
                                   )
                            );
                    }
                })
                ->limit(10)
                ->get();
            $source = 'database_fallback';
        }

        $data = $variants->map(fn ($v) => [
            'id'           => $v->id,
            'label'        => "{$v->year} {$v->model->make->name} {$v->model->name} {$v->name}",
            'year'         => $v->year,
            'make'         => $v->model->make->name,
            'model'        => $v->model->name,
            'variant'      => $v->name,
            'make_slug'    => $v->model->make->slug,
            'model_slug'   => $v->model->slug,
            'body_type'    => $v->body_type,
            'engine'       => $v->engine,
            'transmission' => $v->transmission,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => ['source' => $source],
        ]);
    }

    // ── GET /api/v1/cars/{slug} ──────────────────────────────

    public function showBySlug(string $slug): JsonResponse
    {
        $cacheKey = "slug:v{$this->catalogVersion()}:{$slug}";

        $result = Cache::remember($cacheKey, self::TTL_SLUG, function () use ($slug) {
            // 1. Try Make
            $make = Make::where('slug', $slug)->active()->first();
            if ($make) {
                return [
                    'type'   => 'make',
                    'make'   => $make,
                    'models' => $make->models()->active()->orderBy('name')->get(['id', 'name', 'slug']),
                ];
            }

            // 2. Try CarModel (direct slug match first)
            $model = CarModel::with('make')->where('slug', $slug)->active()->first();

            // 3. Try "make-slug-model-slug" compound pattern
            if (! $model) {
                foreach (Make::active()->get(['id', 'slug']) as $m) {
                    if (str_starts_with($slug, $m->slug . '-')) {
                        $modelSlug = substr($slug, strlen($m->slug) + 1);
                        $model = CarModel::with('make')
                            ->where('make_id', $m->id)
                            ->where('slug', $modelSlug)
                            ->active()
                            ->first();
                        if ($model) break;
                    }
                }
            }

            if ($model) {
                return [
                    'type'  => 'model',
                    'make'  => $model->make,
                    'model' => $model,
                ];
            }

            // 4. Try Branch location page
            $branch = Branch::where('is_active', true)
                ->where(fn ($q) => $q->where('slug', $slug)
                    ->orWhere('slug', 'sell-my-car-' . $slug))
                ->first();

            if ($branch) {
                return ['type' => 'location', 'branch' => $branch];
            }

            return null;
        });

        if (! $result) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }
}

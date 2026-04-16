<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Make;
use App\Models\CarModel;
use App\Models\Variant;
use App\Models\Branch;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function years()
    {
        $years = Variant::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return response()->json([
            'success' => true,
            'data' => $years
        ]);
    }

    public function makes(Request $request)
    {
        $year = $request->query('year');

        $query = Make::where('is_active', true);

        if ($year) {
            $query->whereHas('models.variants', function ($q) use ($year) {
                $q->where('year', $year);
            });
        }

        $makes = $query->orderBy('name')->get(['id', 'name', 'slug', 'logo_url']);

        return response()->json([
            'success' => true,
            'data' => $makes
        ]);
    }

    public function models(Request $request)
    {
        $makeId = $request->query('make_id');
        $year = $request->query('year');

        if (!$makeId) {
            return response()->json(['success' => false, 'message' => 'make_id is required'], 400);
        }

        $query = CarModel::where('make_id', $makeId)->where('is_active', true);

        if ($year) {
            $query->whereHas('variants', function ($q) use ($year) {
                $q->where('year', $year);
            });
        }

        $models = $query->orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    public function variants(Request $request)
    {
        $modelId = $request->query('model_id');
        $year = $request->query('year');

        if (!$modelId || !$year) {
            return response()->json(['success' => false, 'message' => 'model_id and year are required'], 400);
        }

        $variants = Variant::where('model_id', $modelId)
            ->where('year', $year)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->query('q');

        if (!$q || strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            // Using Laravel Scout search
            $variants = Variant::search($q)
                ->take(10)
                ->get();

            // Load relationships for formatting
            $variants->load(['model.make']);

            $formatted = $variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => "{$v->year} {$v->model->make->name} {$v->model->name} {$v->name}",
                    'year' => $v->year,
                    'make' => $v->model->make->name,
                    'model' => $v->model->name,
                    'variant' => $v->name,
                    'body_type' => $v->body_type,
                    'engine' => $v->engine,
                    'transmission' => $v->transmission,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            // Fallback to database search if Meilisearch is unavailable
            $variants = Variant::with(['model.make'])
                ->where(function ($query) use ($q) {
                    if (is_numeric($q) && strlen($q) === 4) {
                        $query->where('year', (int)$q);
                    } else {
                        $query->where('name', 'ilike', "%{$q}%")
                            ->orWhereHas('model', function ($query) use ($q) {
                                $query->where('name', 'ilike', "%{$q}%")
                                    ->orWhereHas('make', function ($query) use ($q) {
                                        $query->where('name', 'ilike', "%{$q}%");
                                    });
                            });
                    }
                })
                ->where('is_active', true)
                ->limit(10)
                ->get();

            $formatted = $variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => "{$v->year} {$v->model->make->name} {$v->model->name} {$v->name}",
                    'year' => $v->year,
                    'make' => $v->model->make->name,
                    'model' => $v->model->name,
                    'variant' => $v->name,
                    'body_type' => $v->body_type,
                    'engine' => $v->engine,
                    'transmission' => $v->transmission,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'meta' => ['source' => 'database_fallback']
            ]);
        }
    }

    public function showBySlug($slug)
    {
        // 1. Try Make
        $make = Make::where('slug', $slug)->where('is_active', true)->first();
        if ($make) {
            $models = $make->models()->where('is_active', true)->orderBy('name')->limit(12)->get(['id', 'name', 'slug']);
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'make',
                    'make' => $make,
                    'models' => $models
                ]
            ]);
        }

        // 2. Try Model (Slug might contain make-model)
        // We look for a model where the slug matches or the concatenation matches
        $model = CarModel::with('make')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$model) {
            // Try matching make-model pattern
            // This is slower but useful for flat URLs like /sell-my-bmw-3-series
            $allMakes = Make::where('is_active', true)->get(['id', 'slug']);
            foreach ($allMakes as $m) {
                if (str_starts_with($slug, $m->slug . '-')) {
                    $modelSlug = substr($slug, strlen($m->slug) + 1);
                    $model = CarModel::where('make_id', $m->id)
                        ->where('slug', $modelSlug)
                        ->where('is_active', true)
                        ->first();
                    if ($model) {
                        $model->load('make');
                        break;
                    }
                }
            }
        }

        if ($model) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'model',
                    'make' => $model->make,
                    'model' => $model
                ]
            ]);
        }

        // 3. Try Branch (Location)
        $branch = Branch::where('slug', $slug)
            ->orWhere('slug', 'sell-my-car-' . $slug)
            ->where('is_active', true)
            ->first();

        if ($branch) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'location',
                    'branch' => $branch
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Car or location data not found'], 404);
    }
}

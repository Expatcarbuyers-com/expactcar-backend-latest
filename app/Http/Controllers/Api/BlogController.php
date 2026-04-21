<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    private const TTL = 3600; // 1 hour

    // ── GET /api/v1/blogs?page=&category= ───────────────────

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page'     => 'nullable|integer|min:1',
            'category' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $page     = (int) $request->query('page', 1);
        $category = $request->query('category');
        $perPage  = (int) $request->query('per_page', 10);
        $cacheKey = "blogs:p{$page}:pp{$perPage}:cat:" . ($category ?? 'all');

        $data = Cache::remember($cacheKey, self::TTL, function () use ($category, $perPage) {
            $query = Blog::published()
                ->with('category')
                ->orderByDesc('published_at');

            if ($category) {
                $query->whereHas('category', fn ($q) =>
                    $q->where('slug', $category)->orWhere('name', 'ilike', $category)
                );
            }

            return $query->paginate($perPage);
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── GET /api/v1/blogs/{slug} ─────────────────────────────

    public function show(string $slug): JsonResponse
    {
        $cacheKey = "blog:{$slug}";

        $blog = Cache::remember($cacheKey, self::TTL, function () use ($slug) {
            return Blog::where('slug', $slug)
                ->published()
                ->with('category')
                ->first();
        });

        if (! $blog) {
            return response()->json(['success' => false, 'message' => 'Blog post not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $blog]);
    }

    // ── GET /api/v1/blogs/categories ────────────────────────

    public function categories(): JsonResponse
    {
        $data = Cache::remember('blog:categories', self::TTL, function () {
            return BlogCategory::withCount(['blogs' => fn ($q) => $q->published()])
                ->having('blogs_count', '>', 0)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UrlRedirect;
use Illuminate\Http\JsonResponse;

class RedirectController extends Controller
{
    /**
     * GET /api/v1/redirects/{slug}
     *
     * Called by Next.js when a /[slug] page returns 404.
     * Returns the new slug so the frontend can issue a 301.
     */
    public function show(string $slug): JsonResponse
    {
        $redirect = UrlRedirect::where('old_slug', $slug)->first();

        if (! $redirect) {
            return response()->json(['success' => false, 'message' => 'No redirect found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'old_slug'     => $redirect->old_slug,
                'new_slug'     => $redirect->new_slug,
                'is_permanent' => $redirect->is_permanent,
                'status_code'  => $redirect->is_permanent ? 301 : 302,
            ],
        ]);
    }
}

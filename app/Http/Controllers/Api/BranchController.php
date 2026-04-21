<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('branches:active', 86400, function () {
            return Branch::where('is_active', true)
                ->orderBy('name')
                ->get();
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}

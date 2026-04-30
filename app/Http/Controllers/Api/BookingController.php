<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ValuationSubmitted;
use App\Models\Booking;
use App\Models\Variant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id'      => 'required|integer|exists:variants,id',
            'mileage'         => 'required|string|max:100',
            'specs'           => 'required|string|max:50',

            'name'            => 'required|string|max:150',
            'phone'           => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]{7,20}$/'],
            'email'           => 'required|email:rfc,dns|max:200',
            'utm_data'        => 'nullable|array',
        ]);

        $variant = Variant::with('model.make')->findOrFail($validated['variant_id']);

        // Upsert: same phone + same variant = update the existing lead, not a duplicate
        $booking = Booking::updateOrCreate(
            [
                'phone'      => $validated['phone'],
                'variant_id' => $validated['variant_id'],
            ],
            [
                'make_name'       => $variant->model->make->name,
                'model_name'      => $variant->model->name,
                'variant_name'    => $variant->name,
                'year'            => $variant->year,
                'mileage'         => $validated['mileage'],
                'specs'           => $validated['specs'],

                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'utm_data'        => $validated['utm_data'] ?? null,
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'status'          => 'pending',
            ]
        );

        // Dispatch email to queue — non-blocking, won't fail the API response
        try {
            Mail::to(config('mail.admin_email', env('ADMIN_EMAIL')))
                ->queue(new ValuationSubmitted($booking));
        } catch (\Exception $e) {
            Log::error('ValuationSubmitted mail dispatch failed', [
                'booking_ref' => $booking->reference_number,
                'error'       => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your valuation request has been submitted.',
            'data'    => [
                'reference_number' => $booking->reference_number,
                'is_update'        => ! $booking->wasRecentlyCreated,
            ],
        ], $booking->wasRecentlyCreated ? 201 : 200);
    }
}

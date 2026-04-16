<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Variant;
use App\Mail\ValuationSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:variants,id',
            'mileage' => 'required|integer|min:0',
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:200',
            'utm_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $variant = Variant::with('model.make')->find($request->variant_id);

        $booking = Booking::create([
            'variant_id' => $variant->id,
            'make_name' => $variant->model->make->name,
            'model_name' => $variant->model->name,
            'variant_name' => $variant->name,
            'year' => $variant->year,
            'mileage' => $request->mileage,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'utm_data' => $request->utm_data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending'
        ]);

        // Send Email Notification to Admin
        try {
            $adminEmail = env('ADMIN_EMAIL', 'info@expatcarbuyers.com');
            Mail::to($adminEmail)->send(new ValuationSubmitted($booking));
        } catch (\Exception $e) {
            \Log::error("Failed to send valuation email: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking submitted successfully',
            'data' => [
                'reference_number' => $booking->reference_number
            ]
        ], 201);
    }
}

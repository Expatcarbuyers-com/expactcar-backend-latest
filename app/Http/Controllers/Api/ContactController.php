<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Mail\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:200',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = Contact::create($request->all());

        // Send Email Notification to Admin
        try {
            $adminEmail = env('ADMIN_EMAIL', 'info@expatcarbuyers.com');
            Mail::to($adminEmail)->send(new ContactInquiry($contact));
        } catch (\Exception $e) {
            \Log::error("Failed to send contact inquiry email: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully. We will get back to you soon.'
        ], 201);
    }
}

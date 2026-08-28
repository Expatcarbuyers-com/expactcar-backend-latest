<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactInquiry;
use App\Models\Contact;
use App\Rules\Recaptcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recaptcha_token' => ['required', 'string', new Recaptcha('contact_submit')],
            'name'            => 'required|string|max:150',
            'email'           => 'required|email:rfc|max:200',
            'phone'           => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]{7,20}$/'],
            'subject'         => 'nullable|string|max:200',
            'message'         => 'required|string|min:10|max:5000',
        ]);

        $contact = Contact::create($validated);

        // 1. Dispatch Admin Notification
        try {
            $adminEmail = config('mail.admin_email') ?: env('ADMIN_EMAIL');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactInquiry($contact));
            }
        } catch (\Throwable $e) {
            Log::error('Admin contact inquiry mail failed', [
                'contact_id' => $contact->id,
                'error'      => $e->getMessage(),
            ]);
        }

        // 2. Dispatch Customer Thank You
        try {
            if (!empty($contact->email)) {
                Mail::to($contact->email)->send(new \App\Mail\ContactThankYou($contact));
            }
        } catch (\Throwable $e) {
            Log::error('Customer contact thank you mail failed', [
                'contact_id' => $contact->id,
                'email'      => $contact->email,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent. We will be in touch shortly.',
        ], 201);
    }
}

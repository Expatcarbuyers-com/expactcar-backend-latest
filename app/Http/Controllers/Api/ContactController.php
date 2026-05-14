<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactInquiry;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email:rfc|max:200',
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]{7,20}$/'],
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $contact = Contact::create($validated);

        try {
            Mail::to(config('mail.admin_email', env('ADMIN_EMAIL')))
                ->queue(new ContactInquiry($contact));

            Mail::to($contact->email)
                ->queue(new \App\Mail\ContactThankYou($contact));
        } catch (\Exception $e) {
            Log::error('Mail dispatch failed', [
                'contact_id' => $contact->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent. We will be in touch shortly.',
        ], 201);
    }
}

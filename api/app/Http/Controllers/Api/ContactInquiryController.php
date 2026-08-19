<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactInquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $now = now();
            $id = DB::table('contact_inquiries')->insertGetId([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => trim($validated['phone']),
                'subject' => trim($validated['subject'] ?? 'General Inquiry'),
                'message' => trim($validated['message']),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Log::info("New contact inquiry #{$id} received from {$validated['name']} ({$validated['email']})");

            return response()->json([
                'success' => true,
                'message' => 'Thank you for reaching out! Your message has been received by our jewellery concierge team. We will get in touch with you shortly.',
                'inquiry_id' => $id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save contact inquiry: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit your message right now. Please call or WhatsApp us directly.',
            ], 500);
        }
    }
}

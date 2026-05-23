<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = trim(strtolower($request->email));

        // Check if already subscribed
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->is_active) {
                return response()->json([
                    'success' => true,
                    'message' => 'You are already subscribed to our newsletter!',
                ]);
            }

            // Reactivate subscription
            $subscriber->update(['is_active' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Welcome back! Your newsletter subscription has been reactivated.',
            ]);
        }

        // Create new subscriber
        NewsletterSubscriber::create([
            'email' => $email,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to the Little Divinity newsletter!',
        ]);
    }
}

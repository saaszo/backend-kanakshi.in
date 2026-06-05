<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerAccessToken;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Services\UploadedImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductReviewController
{
    public function index(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data' => null,
            ], 404);
        }

        $publishedReviews = ProductReview::query()
            ->with(['user:id,name'])
            ->where('product_id', $product->id)
            ->where('is_published', true)
            ->latest()
            ->get();

        $breakdown = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];

        foreach ($publishedReviews as $review) {
            $rating = max(1, min(5, (int) $review->rating));
            $breakdown[$rating]++;
        }

        $viewer = $this->resolveCustomerFromRequest($request);
        $viewerReview = null;
        $eligibility = [
            'is_authenticated' => $viewer !== null,
            'has_purchased' => false,
            'can_submit' => false,
            'reason' => 'Sign in with the customer account that purchased this product to leave a review.',
        ];

        if ($viewer) {
            $orderItem = $this->resolveEligibleOrderItem($viewer->id, $product->id);
            $existingReview = ProductReview::query()
                ->where('product_id', $product->id)
                ->where('user_id', $viewer->id)
                ->first();

            $eligibility['has_purchased'] = $orderItem !== null;

            if ($existingReview) {
                $eligibility['reason'] = $existingReview->is_published
                    ? 'You have already reviewed this product.'
                    : 'Your review has been submitted and is waiting for admin approval.';
                $viewerReview = $this->formatReview($existingReview, true);
            } elseif ($orderItem) {
                $eligibility['can_submit'] = true;
                $eligibility['reason'] = null;
            } else {
                $eligibility['reason'] = 'Only customers who purchased this product can leave a review.';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product reviews fetched successfully.',
            'data' => [
                'summary' => [
                    'avg_rating' => round((float) $product->avg_rating, 1),
                    'review_count' => (int) $product->review_count,
                    'rating_breakdown' => $breakdown,
                ],
                'items' => $publishedReviews->map(fn (ProductReview $review): array => $this->formatReview($review)),
                'eligibility' => $eligibility,
                'viewer_review' => $viewerReview,
            ],
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Please sign in to submit a review.',
            ], 401);
        }

        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        if (ProductReview::query()->where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $orderItem = $this->resolveEligibleOrderItem($user->id, $product->id);

        if (! $orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Only customers who purchased this product can leave a review.',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:8', 'max:2000'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $imageUrls = [];

        foreach ($request->file('images', []) as $file) {
            if ($file instanceof UploadedFile) {
                $imageUrls[] = $this->storeReviewImage($file);
            }
        }

        $review = ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'rating' => (int) $validated['rating'],
            'comment' => trim((string) $validated['comment']),
            'images' => $imageUrls,
            'is_verified_purchase' => true,
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. It will appear after admin approval.',
            'data' => [
                'review' => $this->formatReview($review, true),
            ],
        ], 201);
    }

    private function resolveEligibleOrderItem(int $userId, int $productId): ?OrderItem
    {
        return OrderItem::query()
            ->with('order')
            ->where('product_id', $productId)
            ->whereHas('order', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->whereIn('status', ['shipped', 'delivered', 'refunded'])
                    ->where(function ($statusQuery): void {
                        $statusQuery
                            ->where('payment_status', 'paid')
                            ->orWhere(function ($codQuery): void {
                                $codQuery->where('payment_method', 'cod')
                                    ->whereIn('status', ['shipped', 'delivered', 'refunded']);
                            });
                    });
            })
            ->latest()
            ->first();
    }

    private function resolveCustomerFromRequest(Request $request): ?User
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return null;
        }

        $token = CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $token) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $token->user;
    }

    private function storeReviewImage(UploadedFile $file): string
    {
        $disk = Storage::disk('public');
        $directory = 'customer/reviews';
        $extension = Str::lower((string) $file->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? $baseName : 'review-image';
        $fileName = Str::limit($baseName, 90, '') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $stored = app(UploadedImageOptimizer::class)->storePublic($file, $directory, $fileName);
        $path = (string) ($stored['path'] ?? '');

        return $disk->url($path);
    }

    private function formatReview(ProductReview $review, bool $includeModeration = false): array
    {
        $payload = [
            'id' => $review->id,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'images' => array_values(array_filter((array) $review->images)),
            'is_verified_purchase' => (bool) $review->is_verified_purchase,
            'customer_name' => $this->maskCustomerName((string) optional($review->user)->name),
            'created_at' => optional($review->created_at)?->toIso8601String(),
        ];

        if ($includeModeration) {
            $payload['is_published'] = (bool) $review->is_published;
            $payload['published_at'] = optional($review->published_at)?->toIso8601String();
            $payload['moderated_at'] = optional($review->moderated_at)?->toIso8601String();
        }

        return $payload;
    }

    private function maskCustomerName(string $name): string
    {
        $clean = trim($name);

        if ($clean === '') {
            return 'Verified Buyer';
        }

        $parts = preg_split('/\s+/', $clean) ?: [];
        $first = $parts[0] ?? 'Customer';
        $last = $parts[1] ?? '';

        if ($last === '') {
            return Str::limit($first, 1, '') . str_repeat('*', max(strlen($first) - 1, 0));
        }

        return $first . ' ' . Str::limit($last, 1, '');
    }
}

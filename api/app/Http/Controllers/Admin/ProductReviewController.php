<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('q'));

        $query = ProductReview::query()->with(['product', 'user', 'order']);

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'hidden') {
            $query->where('is_published', false)->whereNotNull('moderated_at');
        } elseif ($status === 'pending') {
            $query->where('is_published', false)->whereNull('moderated_at');
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('comment', 'like', $term)
                    ->orWhereHas('product', function ($productQuery) use ($term): void {
                        $productQuery->where('name', 'like', $term);
                    })
                    ->orWhereHas('user', function ($userQuery) use ($term): void {
                        $userQuery->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('order', function ($orderQuery) use ($term): void {
                        $orderQuery->where('order_number', 'like', $term);
                    });
            });
        }

        return view('admin.reviews.index', [
            'reviews' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
            'stats' => [
                'total' => ProductReview::query()->count(),
                'published' => ProductReview::query()->where('is_published', true)->count(),
                'pending' => ProductReview::query()->where('is_published', false)->whereNull('moderated_at')->count(),
                'hidden' => ProductReview::query()->where('is_published', false)->whereNotNull('moderated_at')->count(),
            ],
        ]);
    }

    public function updateVisibility(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:publish,hide'],
        ]);

        $isPublish = $validated['action'] === 'publish';

        $review->forceFill([
            'is_published' => $isPublish,
            'published_at' => $isPublish ? now() : null,
            'moderated_at' => now(),
            'moderated_by' => Auth::id(),
        ])->save();

        ProductReview::refreshProductMetrics((int) $review->product_id);

        return back()->with('status', $isPublish ? 'Review published successfully.' : 'Review hidden successfully.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $productId = (int) $review->product_id;
        $review->delete();

        ProductReview::refreshProductMetrics($productId);

        return back()->with('status', 'Review deleted successfully.');
    }
}

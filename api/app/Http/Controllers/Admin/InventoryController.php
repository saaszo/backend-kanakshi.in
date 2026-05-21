<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $query = Product::query()->with('category');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $term = '%' . $search . '%';
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        return view('admin.inventory.index', [
            'products' => $query->orderBy('name')->get(),
            'stats' => [
                'total_products' => Product::query()->count(),
                'total_units' => (int) Product::query()->sum('stock'),
                'low_stock' => Product::query()->whereBetween('stock', [1, 5])->count(),
                'out_of_stock' => Product::query()->where('stock', '<=', 0)->count(),
            ],
            'search' => $search,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update([
            'stock' => $validated['stock'],
        ]);

        return back()->with('status', "Inventory updated for {$product->name}.");
    }
}

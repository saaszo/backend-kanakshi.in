<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductCatalogService
{
    public function categories(Request $request): array
    {
        if (!Schema::hasTable('categories')) {
            return [];
        }

        $limit = min(max(1, (int) $request->integer('limit', 12)), 48);

        return Category::query()
            ->select([
                'id',
                'parent_id',
                'name',
                'slug',
                'image',
                'description',
                'sort_order',
                'is_active',
            ])
            ->whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function paginate(Request $request): array
    {
        if (!Schema::hasTable('products')) {
            return $this->emptyResult();
        }

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(max(1, (int) $request->integer('per_page', 12)), 48);

        $query = Product::query()
            ->from('products as p')
            ->select([
                'p.*',
                'c.name as category_name',
                'c.slug as category_slug',
            ])
            ->selectRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) as effective_price')
            ->where('p.is_active', 1);

        if (Schema::hasTable('categories')) {
            $query->leftJoin('categories as c', 'c.id', '=', 'p.category_id');
        } else {
            $query->selectRaw('NULL as category_name, NULL as category_slug');
        }

        if ($request->filled('category')) {
            $query->whereExists(function ($subQuery) use ($request): void {
                $subQuery->selectRaw('1')
                    ->from('categories as cat')
                    ->whereColumn('cat.id', 'p.category_id')
                    ->where('cat.slug', $request->string('category')->toString());
            });
        }

        if ($request->filled('ids')) {
            $ids = collect(explode(',', $request->string('ids')->toString()))
                ->map(fn (string $id): int => (int) trim($id))
                ->filter(fn (int $id): bool => $id > 0)
                ->values()
                ->all();

            if ($ids !== []) {
                $query->whereIn('p.id', $ids);
            }
        }

        if ($request->boolean('featured')) {
            $query->where('p.is_featured', 1);
        }

        if ($request->filled('q')) {
            $term = '%' . trim($request->string('q')->toString()) . '%';

            $query->where(function ($searchQuery) use ($term): void {
                $searchQuery->where('p.name', 'like', $term)
                    ->orWhere('p.description', 'like', $term)
                    ->orWhere('p.short_desc', 'like', $term);
            });
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) >= ?', [(float) $request->input('min_price')]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) <= ?', [(float) $request->input('max_price')]);
        }

        match ($request->string('sort', 'newest')->toString()) {
            'price_asc' => $query->orderByRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) DESC'),
            'popular' => $query->orderByDesc('p.total_sold'),
            'rating' => $query->orderByDesc('p.avg_rating'),
            default => $query->orderByDesc('p.created_at'),
        };

        $results = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
        ];
    }

    public function findBySlug(string $slug): ?array
    {
        if (!Schema::hasTable('products')) {
            return null;
        }

        $query = Product::query()
            ->from('products as p')
            ->select([
                'p.*',
                'c.name as category_name',
                'c.slug as category_slug',
            ])
            ->selectRaw('COALESCE(NULLIF(p.sale_price, 0), p.price) as effective_price')
            ->where('p.slug', $slug)
            ->where('p.is_active', 1);

        if (Schema::hasTable('categories')) {
            $query->leftJoin('categories as c', 'c.id', '=', 'p.category_id');
        } else {
            $query->selectRaw('NULL as category_name, NULL as category_slug');
        }

        $product = $query->first();

        return $product?->toArray();
    }

    private function emptyResult(): array
    {
        return [
            'items' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 12,
                'total' => 0,
                'last_page' => 1,
            ],
        ];
    }
}

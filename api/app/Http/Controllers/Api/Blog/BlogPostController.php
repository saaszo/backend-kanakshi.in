<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogAuthor;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['author', 'category', 'tags'])
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');

        // Filter by Category
        if ($request->has('category')) {
            $categorySlug = $request->query('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by Tag
        if ($request->has('tag')) {
            $tagSlug = $request->query('tag');
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Filter by Author
        if ($request->has('author')) {
            $authorSlug = $request->query('author');
            $query->whereHas('author', function ($q) use ($authorSlug) {
                $q->where('slug', $authorSlug);
            });
        }

        // Search by keyword
        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $perPage = $request->query('per_page', 9);
        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Blog posts retrieved successfully.',
            'data' => $posts,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Blog post not found.',
                'data' => null,
            ], 404);
        }

        // Fetch related products if any exist
        $relatedProducts = [];
        if (!empty($post->related_products_json) && is_array($post->related_products_json)) {
            $relatedProducts = Product::whereIn('id', $post->related_products_json)
                ->where('is_active', true)
                ->get();
        }

        // Fetch related blog posts (from the same category, excluding current post, limit 3)
        $relatedPosts = BlogPost::with(['author', 'category'])
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // If not enough related posts in same category, backfill with newest posts
        if ($relatedPosts->count() < 3) {
            $excludeIds = $relatedPosts->pluck('id')->push($post->id)->toArray();
            $extraPosts = BlogPost::with(['author', 'category'])
                ->where('status', 'published')
                ->where('published_at', '<=', now())
                ->whereNotIn('id', $excludeIds)
                ->orderBy('published_at', 'desc')
                ->limit(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($extraPosts);
        }

        // Expose post as data, including its related products and related posts
        $postData = $post->toArray();
        $postData['related_products'] = $relatedProducts;
        $postData['related_posts'] = $relatedPosts;

        return response()->json([
            'success' => true,
            'message' => 'Blog post retrieved successfully.',
            'data' => $postData,
        ]);
    }
}

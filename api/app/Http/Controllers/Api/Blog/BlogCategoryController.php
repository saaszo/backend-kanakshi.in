<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogAuthor;
use Illuminate\Http\JsonResponse;

class BlogCategoryController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::withCount(['posts' => function ($query) {
            $query->where('status', 'published')->where('published_at', '<=', now());
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully.',
            'data' => $categories,
        ]);
    }

    public function tags(): JsonResponse
    {
        $tags = BlogTag::withCount(['posts' => function ($query) {
            $query->where('status', 'published')->where('published_at', '<=', now());
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Tags fetched successfully.',
            'data' => $tags,
        ]);
    }

    public function showTag(string $slug): JsonResponse
    {
        $tag = BlogTag::where('slug', $slug)->first();

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tag fetched successfully.',
            'data' => $tag,
        ]);
    }

    public function authors(): JsonResponse
    {
        $authors = BlogAuthor::withCount(['posts' => function ($query) {
            $query->where('status', 'published')->where('published_at', '<=', now());
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Authors fetched successfully.',
            'data' => $authors,
        ]);
    }

    public function showCategory(string $slug): JsonResponse
    {
        $category = BlogCategory::where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category fetched successfully.',
            'data' => $category,
        ]);
    }

    public function showAuthor(string $slug): JsonResponse
    {
        $author = BlogAuthor::where('slug', $slug)->first();

        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Author fetched successfully.',
            'data' => $author,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = BlogTag::query()->withCount('posts')->latest()->paginate(15);
        return view('admin.blog.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_tags,slug'],
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);

        BlogTag::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.blog.tags.index')->with('success', 'Blog Tag created successfully.');
    }

    public function update(Request $request, BlogTag $tag)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_tags,slug,' . $tag->id],
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);

        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.blog.tags.index')->with('success', 'Blog Tag updated successfully.');
    }

    public function destroy(BlogTag $tag)
    {
        // Detach tag from posts and delete
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')->with('success', 'Blog Tag deleted successfully.');
    }
}

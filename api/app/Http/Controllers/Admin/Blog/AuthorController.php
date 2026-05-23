<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Models\BlogAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    use HandlesAdminUploads;

    public function index()
    {
        $authors = BlogAuthor::query()->withCount('posts')->latest()->paginate(15);
        return view('admin.blog.authors.index', compact('authors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_authors,slug'],
            'bio' => ['nullable', 'string'],
            'avatar_upload' => ['nullable', 'image', 'max:2048'],
            'avatar_alt' => ['nullable', 'string', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);

        $avatarUrl = null;
        if ($request->hasFile('avatar_upload')) {
            $avatarUrl = $this->storeAdminUpload($request->file('avatar_upload'), 'authors', 'Author avatar');
        }

        BlogAuthor::create([
            'name' => $request->name,
            'slug' => $slug,
            'bio' => $request->bio,
            'avatar' => $avatarUrl,
            'avatar_alt' => $request->avatar_alt,
            'twitter_handle' => $request->twitter_handle,
        ]);

        return redirect()->route('admin.blog.authors.index')->with('success', 'Blog Author created successfully.');
    }

    public function update(Request $request, BlogAuthor $author)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_authors,slug,' . $author->id],
            'bio' => ['nullable', 'string'],
            'avatar_upload' => ['nullable', 'image', 'max:2048'],
            'avatar_alt' => ['nullable', 'string', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($author->name);

        $avatarUrl = $author->avatar;
        if ($request->hasFile('avatar_upload')) {
            $avatarUrl = $this->storeAdminUpload($request->file('avatar_upload'), 'authors', 'Author avatar');
        }

        $author->update([
            'name' => $request->name,
            'slug' => $slug,
            'bio' => $request->bio,
            'avatar' => $avatarUrl,
            'avatar_alt' => $request->avatar_alt,
            'twitter_handle' => $request->twitter_handle,
        ]);

        return redirect()->route('admin.blog.authors.index')->with('success', 'Blog Author updated successfully.');
    }

    public function destroy(BlogAuthor $author)
    {
        if ($author->posts()->exists()) {
            return redirect()->route('admin.blog.authors.index')->with('error', 'Cannot delete Author because they have written active posts.');
        }

        $author->delete();
        return redirect()->route('admin.blog.authors.index')->with('success', 'Blog Author deleted successfully.');
    }
}

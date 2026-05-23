<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogAuthor;
use App\Models\BlogRevision;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use HandlesAdminUploads;

    public function index(Request $request)
    {
        $query = BlogPost::query()->with(['category', 'author'])->latest();

        // Search by title/slug/keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('primary_keyword', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('blog_category_id', $request->category_id);
        }

        // Author Filter
        if ($request->filled('author_id')) {
            $query->where('blog_author_id', $request->author_id);
        }

        $posts = $query->paginate(15);
        $categories = BlogCategory::orderBy('name')->get();
        $authors = BlogAuthor::orderBy('name')->get();

        return view('admin.blog.posts.index', compact('posts', 'categories', 'authors'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $authors = BlogAuthor::orderBy('name')->get();
        $products = Product::orderBy('name')->get(); // for linking related products

        return view('admin.blog.posts.create', compact('categories', 'tags', 'authors', 'products'));
    }

    public function store(Request $request)
    {
        $isPublished = $request->status === 'published';

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'],
            'content' => ['required', 'string'],
            'excerpt' => [$isPublished ? 'required' : 'nullable', 'string'],
            'primary_keyword' => [$isPublished ? 'required' : 'nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'string'],
            'blog_author_id' => ['required', 'exists:blog_authors,id'],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'status' => ['required', 'string', 'in:draft,scheduled,published'],
            'published_at' => [$request->status === 'scheduled' ? 'required' : 'nullable', 'date'],
            'featured_image_upload' => ['nullable', 'image', 'max:5120'],
            'featured_image_alt' => [$isPublished && $request->hasFile('featured_image_upload') ? 'required' : 'nullable', 'string', 'max:255'],
            
            // SEO Meta fields
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url'],
            'seo_noindex' => ['nullable', 'boolean'],
            'seo_nofollow' => ['nullable', 'boolean'],
            'schema_type' => ['nullable', 'string', 'max:255'],
            
            // FAQ array validation
            'faq_question' => ['nullable', 'array'],
            'faq_question.*' => ['nullable', 'string'],
            'faq_answer' => ['nullable', 'array'],
            'faq_answer.*' => ['nullable', 'string'],
            
            // Related products
            'related_products' => ['nullable', 'array'],
            'related_products.*' => ['exists:products,id'],
            
            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:blog_tags,id'],
        ];

        $request->validate($rules);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);

        $imageUrl = null;
        if ($request->hasFile('featured_image_upload')) {
            $imageUrl = $this->storeAdminUpload($request->file('featured_image_upload'), 'blog', 'Blog featured image');
        }

        // Parse FAQs
        $faqJson = [];
        if ($request->filled('faq_question')) {
            foreach ($request->faq_question as $index => $question) {
                if (!empty($question) && !empty($request->faq_answer[$index])) {
                    $faqJson[] = [
                        'question' => $question,
                        'answer' => $request->faq_answer[$index]
                    ];
                }
            }
        }

        // Set published_at based on status
        $publishedAt = $request->published_at;
        if ($request->status === 'published' && !$request->filled('published_at')) {
            $publishedAt = now();
        }

        $readingTime = $this->calculateReadingTime($request->content);

        $post = BlogPost::create([
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'featured_image' => $imageUrl,
            'featured_image_alt' => $request->featured_image_alt,
            'blog_author_id' => $request->blog_author_id,
            'blog_category_id' => $request->blog_category_id,
            'status' => $request->status,
            'published_at' => $publishedAt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'canonical_url' => $request->canonical_url,
            'og_title' => $request->meta_title ?? $request->title,
            'og_description' => $request->meta_description ?? $request->excerpt,
            'og_image' => $imageUrl,
            'twitter_title' => $request->meta_title ?? $request->title,
            'twitter_description' => $request->meta_description ?? $request->excerpt,
            'twitter_image' => $imageUrl,
            'primary_keyword' => $request->primary_keyword,
            'secondary_keywords' => $request->secondary_keywords,
            'reading_time' => $readingTime,
            'seo_noindex' => $request->boolean('seo_noindex'),
            'seo_nofollow' => $request->boolean('seo_nofollow'),
            'schema_type' => $request->schema_type ?? 'BlogPosting',
            'faq_json' => $faqJson,
            'related_products_json' => $request->related_products ?? [],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        // Sync Tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        // Create Revision
        BlogRevision::create([
            'blog_post_id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'faq_json' => $faqJson,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog Post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $authors = BlogAuthor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        $revisions = $post->revisions()->with('updater')->latest()->get();

        return view('admin.blog.posts.edit', compact('post', 'categories', 'tags', 'authors', 'products', 'revisions'));
    }

    public function show(BlogPost $post)
    {
        return redirect()->route('admin.blog.posts.edit', $post);
    }

    public function update(Request $request, BlogPost $post)
    {
        $isPublished = $request->status === 'published';

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug,' . $post->id],
            'content' => ['required', 'string'],
            'excerpt' => [$isPublished ? 'required' : 'nullable', 'string'],
            'primary_keyword' => [$isPublished ? 'required' : 'nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'string'],
            'blog_author_id' => ['required', 'exists:blog_authors,id'],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'status' => ['required', 'string', 'in:draft,scheduled,published'],
            'published_at' => [$request->status === 'scheduled' ? 'required' : 'nullable', 'date'],
            'featured_image_upload' => ['nullable', 'image', 'max:5120'],
            'featured_image_alt' => [$isPublished && ($request->hasFile('featured_image_upload') || $post->featured_image) ? 'required' : 'nullable', 'string', 'max:255'],
            
            // SEO Meta fields
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url'],
            'seo_noindex' => ['nullable', 'boolean'],
            'seo_nofollow' => ['nullable', 'boolean'],
            'schema_type' => ['nullable', 'string', 'max:255'],
            
            // FAQ array validation
            'faq_question' => ['nullable', 'array'],
            'faq_question.*' => ['nullable', 'string'],
            'faq_answer' => ['nullable', 'array'],
            'faq_answer.*' => ['nullable', 'string'],
            
            // Related products
            'related_products' => ['nullable', 'array'],
            'related_products.*' => ['exists:products,id'],
            
            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:blog_tags,id'],
        ];

        $request->validate($rules);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);

        $imageUrl = $post->featured_image;
        if ($request->hasFile('featured_image_upload')) {
            $imageUrl = $this->storeAdminUpload($request->file('featured_image_upload'), 'blog', 'Blog featured image');
        }

        // Parse FAQs
        $faqJson = [];
        if ($request->filled('faq_question')) {
            foreach ($request->faq_question as $index => $question) {
                if (!empty($question) && !empty($request->faq_answer[$index])) {
                    $faqJson[] = [
                        'question' => $question,
                        'answer' => $request->faq_answer[$index]
                    ];
                }
            }
        }

        // Set published_at based on status
        $publishedAt = $request->published_at ?? $post->published_at;
        if ($request->status === 'published' && !$publishedAt) {
            $publishedAt = now();
        }

        $readingTime = $this->calculateReadingTime($request->content);

        $post->update([
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'featured_image' => $imageUrl,
            'featured_image_alt' => $request->featured_image_alt,
            'blog_author_id' => $request->blog_author_id,
            'blog_category_id' => $request->blog_category_id,
            'status' => $request->status,
            'published_at' => $publishedAt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'canonical_url' => $request->canonical_url,
            'og_title' => $request->meta_title ?? $request->title,
            'og_description' => $request->meta_description ?? $request->excerpt,
            'og_image' => $imageUrl,
            'twitter_title' => $request->meta_title ?? $request->title,
            'twitter_description' => $request->meta_description ?? $request->excerpt,
            'twitter_image' => $imageUrl,
            'primary_keyword' => $request->primary_keyword,
            'secondary_keywords' => $request->secondary_keywords,
            'reading_time' => $readingTime,
            'seo_noindex' => $request->boolean('seo_noindex'),
            'seo_nofollow' => $request->boolean('seo_nofollow'),
            'schema_type' => $request->schema_type ?? 'BlogPosting',
            'faq_json' => $faqJson,
            'related_products_json' => $request->related_products ?? [],
            'updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        // Sync Tags
        $post->tags()->sync($request->tags ?? []);

        // Create Revision
        BlogRevision::create([
            'blog_post_id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'faq_json' => $faqJson,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog Post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        $post->tags()->detach();
        $post->delete();

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog Post deleted successfully.');
    }

    public function preview(BlogPost $post)
    {
        return view('admin.blog.posts.preview', compact('post'));
    }

    public function restoreRevision(Request $request, BlogPost $post, BlogRevision $revision)
    {
        if ($revision->blog_post_id !== $post->id) {
            abort(403);
        }

        $post->update([
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'content' => $revision->content,
            'faq_json' => $revision->faq_json,
            'updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        // Create a fresh revision representing this restore action
        BlogRevision::create([
            'blog_post_id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'faq_json' => $post->faq_json,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.blog.posts.edit', $post)->with('success', 'Blog Post content successfully restored to revision from ' . $revision->created_at->format('M d, Y H:i'));
    }

    protected function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / 200); // 200 words per minute average reading speed
        return max(1, (int)$minutes);
    }
}

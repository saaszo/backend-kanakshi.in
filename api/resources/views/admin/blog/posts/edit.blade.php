@extends('admin.layout')

@section('title', 'Edit Blog Post')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Page Head -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Editorial Suite</div>
                        <h2>Edit Article</h2>
                        <p class="lead" style="margin-top:8px;">Refine content copy, manage revisions, and optimize SEO fields.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.blog.posts.preview', $post) }}" target="_blank" class="button secondary small">
                            <i class="bi bi-eye"></i>
                            <span>Preview</span>
                        </a>
                        <a href="{{ route('admin.blog.posts.index') }}" class="button secondary small">
                            <i class="bi bi-arrow-left"></i>
                            <span>All Articles</span>
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>{{ session('success') }}</p>
    </div>
</div>
                @endif
                @if ($errors->any())
                    <div class="admin-errors">
                        <h4 class="mb-2"><i class="bi bi-x-circle-fill"></i> Validation Errors</h4>
                        <ul class="rule-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.blog.posts.update', $post) }}" enctype="multipart/form-data" class="split-grid admin-split-layout" data-auto-slug-form>
                    @csrf
                    @method('PUT')

                    <!-- Left Column: Primary Content -->
                    <div class="admin-fields">
                        <section class="admin-section">
                            <h3 class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                <span>Article Details</span>
                            </h3>

                            <div class="field">
                                <label>Article Title <span class="text-danger">*</span></label>
                                <input name="title" value="{{ old('title', $post->title) }}" required placeholder="e.g. 5 Benefits of Pure Brass Utensils in Your Kitchen" data-slug-source id="editor-title" />
                            </div>

                            <div class="field">
                                <label>Slug (SEO URL Segment) <span class="text-danger">*</span></label>
                                <input name="slug" value="{{ old('slug', $post->slug) }}" placeholder="e.g. benefits-pure-brass-utensils-kitchen" data-slug-target id="editor-slug" />
                            </div>

                            <div class="form-grid">
                                <div class="field">
                                    <label>Topic Category <span class="text-danger">*</span></label>
                                    <select name="blog_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Author Assignment <span class="text-danger">*</span></label>
                                    <select name="blog_author_id" required>
                                        <option value="">Select Author</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" @selected(old('blog_author_id', $post->blog_author_id) == $author->id)>{{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="field">
                                <label>Article Tags</label>
                                <div class="row g-2 p-2 border rounded" style="max-height: 120px; overflow-y: auto; background: #fff;">
                                    @php
                                        $postTags = $post->tags->pluck('id')->toArray();
                                    @endphp
                                    @foreach($tags as $tag)
                                        <div class="col-md-4 col-sm-6">
                                            <label class="checkbox-row compact">
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(is_array(old('tags', $postTags)) && in_array($tag->id, old('tags', $postTags)))>
                                                <span>{{ $tag->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="field">
                                <label>Long-Form Content <span class="text-danger">*</span></label>
                                <p class="muted mb-2" style="font-size: 12px;"><i class="bi bi-info-circle"></i> Use standard HTML headings <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, lists, and links for maximum editorial design formatting.</p>
                                <textarea name="content" id="editor-content" class="code" required placeholder="Write your long-form article content here in rich formatting or clean HTML structure...">{{ old('content', $post->content) }}</textarea>
                            </div>
                        </section>

                        <!-- FAQ Block Builder -->
                        <section class="admin-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="d-flex align-items-center gap-2 mb-0">
                                    <i class="bi bi-question-circle text-primary"></i>
                                    <span>FAQ Schema Blocks Builder</span>
                                </h3>
                                <button type="button" class="button secondary small" id="add-faq-btn" style="padding: 4px 10px; font-size: 11px;">
                                    <i class="bi bi-plus-circle"></i> Add FAQ Row
                                </button>
                            </div>
                            <p class="muted mb-3" style="font-size: 12px;">FAQ schema details will automatically map to search engine rich results snippets for your blog posts.</p>
                            
                            <div id="faq-blocks-container" class="preview-list">
                                <!-- Appended dynamically via JS -->
                            </div>
                        </section>

                        <!-- Related Storefront Products Selector -->
                        <section class="admin-section">
                            <h3 class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-cart-plus text-primary"></i>
                                <span>Link Related E-Commerce Accents</span>
                            </h3>
                            <p class="muted mb-3" style="font-size: 12px;">Linked accents will render as luxury direct-purchase cards inside the storefront article, boosting conversion rates.</p>
                            
                            <div class="field">
                                <label>Select Catalog Products to Feature</label>
                                <div class="row g-2 p-3 border rounded" style="max-height: 180px; overflow-y: auto; background: #fff;">
                                    @php
                                        $linkedProducts = $post->related_products_json ?? [];
                                    @endphp
                                    @foreach($products as $product)
                                        <div class="col-md-6 col-sm-12">
                                            <label class="checkbox-row compact">
                                                <input type="checkbox" name="related_products[]" value="{{ $product->id }}" @checked(is_array(old('related_products', $linkedProducts)) && in_array($product->id, old('related_products', $linkedProducts)))>
                                                <span>{{ $product->name }} (₹{{ number_format($product->effective_price ?? $product->price) }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: SEO Controls, Revisions & Guidelines -->
                    <div class="admin-fields" style="position: sticky; top: 24px;">
                        <!-- Status and Publishing Actions -->
                        <section class="admin-section border-primary" style="background: radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.02), transparent);">
                            <h3 class="mb-3">Publish Control</h3>
                            
                            <div class="field">
                                <label>Workflow Status</label>
                                <select name="status" id="editor-status" required>
                                    <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft (Hidden from Store)</option>
                                    <option value="scheduled" @selected(old('status', $post->status) === 'scheduled')>Scheduled (Publish Future Time)</option>
                                    <option value="published" @selected(old('status', $post->status) === 'published')>Published (Live Instantly)</option>
                                </select>
                            </div>

                            <div class="field" id="schedule-date-wrap" style="display: none;">
                                <label>Schedule Publish Date & Time</label>
                                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" id="editor-published-at" />
                            </div>

                            <div class="button-row mt-3">
                                <button class="button small w-100" type="submit">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </section>

                        <!-- Revisions History -->
                        <section class="admin-section">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary"></i>
                                <span>Revisions History</span>
                            </h3>
                            <div class="row g-2 p-2 border rounded" style="max-height: 250px; overflow-y: auto; background: #fff; font-size: 13px;">
                                @forelse($revisions as $revision)
                                    <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1 min-width-0 me-2">
                                            <strong class="d-block text-truncate" style="color: var(--heading);">By {{ $revision->updater->name ?? 'System' }}</strong>
                                            <span class="text-soft font-monospace" style="font-size:11px;">{{ $revision->created_at->format('M d, Y H:i:s') }}</span>
                                        </div>
                                        <form method="POST" action="{{ route('admin.blog.posts.restore-revision', [$post, $revision]) }}" onsubmit="return confirm('Restore article content to this revision? Current edits will be saved as a new revision.')" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="button secondary small py-1 px-2" style="font-size: 11px;">Restore</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-soft w-100">No revisions logged yet.</div>
                                @endforelse
                            </div>
                        </section>

                        <!-- Editorial Governance Checklist -->
                        <section class="admin-section">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-warning"></i>
                                <span>SEO Guidelines Panel</span>
                            </h3>
                            <div class="preview-list border rounded p-3" style="background: #f8fafc; font-size: 13px;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span>Word Count:</span>
                                    <span id="badge-word-count" class="admin-badge primary">0 words</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span>Primary Keyword:</span>
                                    <span id="badge-keyword" class="admin-badge danger">Missing</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span>Excerpt check:</span>
                                    <span id="badge-excerpt" class="admin-badge danger">Missing</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span>Featured Alt check:</span>
                                    <span id="badge-image-alt" class="admin-badge danger">Missing</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Headings Check:</span>
                                    <span id="badge-headings" class="admin-badge warning">No H2 Tags</span>
                                </div>
                                <div class="mt-3 pt-3 border-top text-center text-soft" style="font-size: 12px;" id="guidelines-message">
                                    Fill out mandatory fields to check publishing compliance.
                                </div>
                            </div>
                        </section>

                        <!-- SEO Metadata -->
                        <section class="admin-section">
                            <h3 class="mb-3">SEO SERP Optimization</h3>

                            <div class="field">
                                <label>Primary Focus Keyword <span class="text-danger" id="keyword-req-star" style="display:none;">*</span></label>
                                <input name="primary_keyword" value="{{ old('primary_keyword', $post->primary_keyword) }}" id="editor-primary-keyword" placeholder="e.g. brass pooja room" />
                            </div>

                            <div class="field">
                                <label>Secondary Keywords (Comma-separated)</label>
                                <input name="secondary_keywords" value="{{ old('secondary_keywords', $post->secondary_keywords) }}" placeholder="e.g. brass ganesha, home mandir design, pooja space" />
                            </div>

                            <div class="field">
                                <label>Brief Excerpt Snippet <span class="text-danger" id="excerpt-req-star" style="display:none;">*</span></label>
                                <textarea name="excerpt" id="editor-excerpt" placeholder="Write a short summary (140-180 characters) to show on catalog listings..." style="min-height: 80px;">{{ old('excerpt', $post->excerpt) }}</textarea>
                            </div>

                            <div class="field">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0">Meta Page Title</label>
                                    <span id="char-count-meta-title" class="font-monospace text-soft" style="font-size: 11px;">0 / 60</span>
                                </div>
                                <input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" id="editor-meta-title" placeholder="Keep within 45-65 characters..." />
                                <div id="warning-meta-title" class="text-soft mt-1" style="font-size: 11px;"></div>
                            </div>

                            <div class="field">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0">Meta Page Description</label>
                                    <span id="char-count-meta-desc" class="font-monospace text-soft" style="font-size: 11px;">0 / 150</span>
                                </div>
                                <textarea name="meta_description" id="editor-meta-desc" placeholder="Keep within 140-160 characters..." style="min-height: 80px;">{{ old('meta_description', $post->meta_description) }}</textarea>
                                <div id="warning-meta-desc" class="text-soft mt-1" style="font-size: 11px;"></div>
                            </div>

                            <div class="field">
                                <label>Featured Post Image</label>
                                @if($post->featured_image)
                                    <div class="mb-2">
                                        <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt }}" class="admin-upload-preview" />
                                    </div>
                                @endif
                                <input type="file" name="featured_image_upload" id="editor-image" accept="image/*" />
                            </div>

                            <div class="field">
                                <label>Image Alt Description <span class="text-danger" id="alt-req-star" style="display:none;">*</span></label>
                                <input name="featured_image_alt" value="{{ old('featured_image_alt', $post->featured_image_alt) }}" id="editor-image-alt" placeholder="e.g. Generational handcrafted brass spice box setup" />
                            </div>

                            <div class="field">
                                <label>Canonical Reference URL</label>
                                <input type="url" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}" placeholder="e.g. https://littledivinity.com/blog/..." />
                            </div>

                            <div class="field">
                                <label>Schema LD Structure type</label>
                                <select name="schema_type">
                                    <option value="BlogPosting" @selected(old('schema_type', $post->schema_type) === 'BlogPosting')>BlogPosting (Standard Blog)</option>
                                    <option value="Article" @selected(old('schema_type', $post->schema_type) === 'Article')>Article (General Essay)</option>
                                    <option value="NewsArticle" @selected(old('schema_type', $post->schema_type) === 'NewsArticle')>NewsArticle (Announcements)</option>
                                </select>
                            </div>

                            <div class="field d-flex gap-4 p-2 bg-light border rounded">
                                <label class="checkbox-row compact mb-0">
                                    <input type="checkbox" name="seo_noindex" value="1" @checked(old('seo_noindex', $post->seo_noindex))>
                                    <span>noindex</span>
                                </label>
                                <label class="checkbox-row compact mb-0">
                                    <input type="checkbox" name="seo_nofollow" value="1" @checked(old('seo_nofollow', $post->seo_nofollow))>
                                    <span>nofollow</span>
                                </label>
                            </div>
                        </section>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            // Slugify Helper
            const slugify = (value) =>
                value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

            // Auto-slug Logic
            document.querySelectorAll('[data-auto-slug-form]').forEach((form) => {
                const slugSource = form.querySelector('[data-slug-source]');
                const slugTarget = form.querySelector('[data-slug-target]');
                if (!slugSource || !slugTarget) return;

                const initialAutoSlug = slugify(slugSource.value || '');
                let slugManual = Boolean(slugTarget.value) && slugTarget.value !== initialAutoSlug;

                slugTarget.addEventListener('input', () => { slugManual = true; });
                slugSource.addEventListener('input', () => {
                    if (!slugManual) {
                        slugTarget.value = slugify(slugSource.value || '');
                        slugTarget.dispatchEvent(new Event('input')); // trigger update checks
                    }
                });
            });

            // Toggle Publishing Date for Scheduled Mode
            const statusSelect = document.getElementById('editor-status');
            const scheduleWrap = document.getElementById('schedule-date-wrap');
            const toggleScheduleWrap = () => {
                if (statusSelect.value === 'scheduled') {
                    scheduleWrap.style.display = 'block';
                    document.getElementById('editor-published-at').setAttribute('required', 'required');
                } else {
                    scheduleWrap.style.display = 'none';
                    document.getElementById('editor-published-at').removeAttribute('required');
                }
            };
            statusSelect.addEventListener('change', toggleScheduleWrap);
            toggleScheduleWrap();

            // FAQ row dynamic appending
            const faqContainer = document.getElementById('faq-blocks-container');
            const addFaqBtn = document.getElementById('add-faq-btn');
            
            const createFaqRow = (questionVal = '', answerVal = '') => {
                const row = document.createElement('div');
                row.className = 'media-slot-card p-3 mb-2 border rounded position-relative';
                row.style.background = '#ffffff';
                row.innerHTML = `
                    <button type="button" class="btn-close btn-close-sm position-absolute" style="top: 10px; right: 10px; font-size:10px; border:none; background:none;" onclick="this.parentElement.remove();" aria-label="Remove FAQ"></button>
                    <div class="field mb-2">
                        <label class="mb-1" style="font-size:11px; text-transform:uppercase;">FAQ Question</label>
                        <input name="faq_question[]" value="${questionVal}" placeholder="e.g. Is pure solid brass microwave safe?" style="padding:8px 10px;" />
                    </div>
                    <div class="field mb-0">
                        <label class="mb-1" style="font-size:11px; text-transform:uppercase;">FAQ Answer</label>
                        <textarea name="faq_answer[]" placeholder="e.g. Microwave waves will spark off metal, solid brass items must not be used in microwaves." style="min-height:50px; padding:8px 10px;"></textarea>
                    </div>
                `;
                if(answerVal) {
                    row.querySelector('textarea').value = answerVal;
                }
                faqContainer.appendChild(row);
            };

            addFaqBtn.addEventListener('click', () => createFaqRow());

            // Pre-populate existing FAQs on load
            @php
                $existingFaqs = $post->faq_json ?? [];
            @endphp
            @foreach($existingFaqs as $faq)
                createFaqRow(
                    {!! json_encode($faq['question']) !!},
                    {!! json_encode($faq['answer']) !!}
                );
            @endforeach

            // Real-Time SEO & Metadata Optimization Evaluators
            const titleInput = document.getElementById('editor-title');
            const keywordInput = document.getElementById('editor-primary-keyword');
            const excerptInput = document.getElementById('editor-excerpt');
            const contentInput = document.getElementById('editor-content');
            const imageInput = document.getElementById('editor-image');
            const imageAltInput = document.getElementById('editor-image-alt');
            
            const metaTitleInput = document.getElementById('editor-meta-title');
            const metaDescInput = document.getElementById('editor-meta-desc');

            const charCountMetaTitle = document.getElementById('char-count-meta-title');
            const charCountMetaDesc = document.getElementById('char-count-meta-desc');

            const warnMetaTitle = document.getElementById('warning-meta-title');
            const warnMetaDesc = document.getElementById('warning-meta-desc');

            const badgeWordCount = document.getElementById('badge-word-count');
            const badgeKeyword = document.getElementById('badge-keyword');
            const badgeExcerpt = document.getElementById('badge-excerpt');
            const badgeImageAlt = document.getElementById('badge-image-alt');
            const badgeHeadings = document.getElementById('badge-headings');
            const guidelinesMsg = document.getElementById('guidelines-message');

            const reqStars = {
                keyword: document.getElementById('keyword-req-star'),
                excerpt: document.getElementById('excerpt-req-star'),
                alt: document.getElementById('alt-req-star')
            };

            const runSeoCheck = () => {
                const isPublished = statusSelect.value === 'published';

                // Display dynamic required asterisks if Status is set to Published
                if(isPublished) {
                    reqStars.keyword.style.display = 'inline';
                    reqStars.excerpt.style.display = 'inline';
                    reqStars.alt.style.display = 'inline';
                } else {
                    reqStars.keyword.style.display = 'none';
                    reqStars.excerpt.style.display = 'none';
                    reqStars.alt.style.display = 'none';
                }

                // 1. Meta Title Counter and Guidelines
                const titleLen = metaTitleInput.value.length;
                charCountMetaTitle.textContent = `${titleLen} / 60`;
                if(titleLen === 0) {
                    warnMetaTitle.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Title missing.</span>`;
                } else if(titleLen < 45) {
                    warnMetaTitle.innerHTML = `<span class="text-warning"><i class="bi bi-chevron-double-up"></i> Too short. Target 45-65 characters.</span>`;
                } else if(titleLen > 65) {
                    warnMetaTitle.innerHTML = `<span class="text-danger"><i class="bi bi-chevron-double-down"></i> Too long. Target 45-65 characters.</span>`;
                } else {
                    warnMetaTitle.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Perfect SERP length!</span>`;
                }

                // 2. Meta Description Counter and Guidelines
                const descLen = metaDescInput.value.length;
                charCountMetaDesc.textContent = `${descLen} / 150`;
                if(descLen === 0) {
                    warnMetaDesc.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Description missing.</span>`;
                } else if(descLen < 130) {
                    warnMetaDesc.innerHTML = `<span class="text-warning"><i class="bi bi-chevron-double-up"></i> Too short. Target 140-160 characters.</span>`;
                } else if(descLen > 165) {
                    warnMetaDesc.innerHTML = `<span class="text-danger"><i class="bi bi-chevron-double-down"></i> Too long. Target 140-160 characters.</span>`;
                } else {
                    warnMetaDesc.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Perfect SERP length!</span>`;
                }

                // 3. Excerpt validation check
                if(excerptInput.value.trim().length > 0) {
                    badgeExcerpt.className = "admin-badge success";
                    badgeExcerpt.textContent = "✓ Filled";
                } else {
                    badgeExcerpt.className = isPublished ? "admin-badge danger" : "admin-badge warning";
                    badgeExcerpt.textContent = isPublished ? "Required" : "Missing";
                }

                // 4. Primary keyword check
                if(keywordInput.value.trim().length > 0) {
                    badgeKeyword.className = "admin-badge success";
                    badgeKeyword.textContent = "✓ Active";
                } else {
                    badgeKeyword.className = isPublished ? "admin-badge danger" : "admin-badge warning";
                    badgeKeyword.textContent = isPublished ? "Required" : "Missing";
                }

                // 5. Featured image ALT check
                const hasImg = imageInput.files.length > 0 || {!! json_encode(!empty($post->featured_image)) !!};
                const hasAlt = imageAltInput.value.trim().length > 0;
                if(hasAlt) {
                    badgeImageAlt.className = "admin-badge success";
                    badgeImageAlt.textContent = "✓ Alt Filled";
                } else if(hasImg && !hasAlt) {
                    badgeImageAlt.className = "admin-badge danger";
                    badgeImageAlt.textContent = "Required";
                } else {
                    badgeImageAlt.className = isPublished ? "admin-badge danger" : "admin-badge warning";
                    badgeImageAlt.textContent = isPublished ? "Required" : "Missing Alt";
                }

                // 6. Word count and structural H2 headings audit check
                const contentText = contentInput.value.trim();
                const wordCount = contentText ? contentText.split(/\s+/).length : 0;
                
                badgeWordCount.textContent = `${wordCount} words`;
                if(wordCount > 1800) {
                    badgeWordCount.className = "admin-badge success";
                    badgeWordCount.textContent = `Deep Guide (${wordCount}w)`;
                } else if(wordCount >= 1000) {
                    badgeWordCount.className = "admin-badge primary";
                    badgeWordCount.textContent = `Standard (${wordCount}w)`;
                } else {
                    badgeWordCount.className = "admin-badge warning";
                }

                // Search for H2 heading tags in content
                const hasH2 = /<h2\b[^>]*>/i.test(contentText);
                if(hasH2) {
                    badgeHeadings.className = "admin-badge success";
                    badgeHeadings.textContent = "✓ H2 Present";
                } else if(wordCount > 1000) {
                    badgeHeadings.className = "admin-badge danger";
                    badgeHeadings.textContent = "Needs H2 Headings";
                } else {
                    badgeHeadings.className = "admin-badge warning";
                    badgeHeadings.textContent = "No H2 tags";
                }

                // Dynamic checklist compliance message
                if(isPublished && (excerptInput.value.trim() === '' || keywordInput.value.trim() === '' || (hasImg && !hasAlt))) {
                    guidelinesMsg.innerHTML = `<span class="text-danger font-weight-bold"><i class="bi bi-x-octagon-fill"></i> Not compliant for Live Publishing. Please fix warnings.</span>`;
                } else {
                    guidelinesMsg.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Compliance checks satisfied!</span>`;
                }
            };

            // Hook Event Listeners
            [statusSelect, titleInput, keywordInput, excerptInput, contentInput, imageInput, imageAltInput, metaTitleInput, metaDescInput].forEach(elem => {
                if(elem) elem.addEventListener('input', runSeoCheck);
            });
            statusSelect.addEventListener('change', runSeoCheck);
            imageInput.addEventListener('change', runSeoCheck);

            // Run initial audit on load
            runSeoCheck();
        })();
    </script>
@endpush

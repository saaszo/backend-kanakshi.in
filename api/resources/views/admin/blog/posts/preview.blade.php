@extends('admin.layout')

@section('title', 'Preview: ' . $post->title)

@section('content')
    <div class="container py-5" style="max-width: 800px; font-family: 'Inter', sans-serif;">
        <div class="mb-4">
            <span class="badge bg-secondary mb-2">CMS PREVIEW MODE</span>
            <h1 style="font-weight: 800; font-size: 2.75rem; line-height: 1.15; color: #0f172a;">{{ $post->title }}</h1>
            
            <div class="d-flex align-items-center gap-3 my-3 text-muted" style="font-size: 14px;">
                @if($post->author)
                    <div class="d-flex align-items-center gap-2">
                        @if($post->author->avatar)
                            <img src="{{ $post->author->avatar }}" alt="{{ $post->author->avatar_alt }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" />
                        @endif
                        <strong>By {{ $post->author->name }}</strong>
                    </div>
                @endif
                <div>•</div>
                <div>Category: <span class="badge bg-light text-dark border">{{ $post->category->name ?? 'Uncategorized' }}</span></div>
                <div>•</div>
                <div>{{ $post->reading_time ?? 1 }} min read</div>
                <div>•</div>
                <div>Status: <span class="badge @if($post->status === 'published') bg-success @elseif($post->status==='scheduled') bg-primary @else bg-warning text-dark @endif">{{ ucfirst($post->status) }}</span></div>
            </div>
        </div>

        @if($post->featured_image)
            <div class="mb-4 text-center">
                <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt }}" class="img-fluid rounded" style="max-height: 480px; width: 100%; object-fit: cover;" />
                <small class="text-muted d-block mt-2">Alt: <em>{{ $post->featured_image_alt }}</em></small>
            </div>
        @endif

        @if($post->excerpt)
            <div class="p-3 bg-light border-start border-primary border-3 rounded mb-4 font-italic" style="font-size: 1.1rem; line-height: 1.6; color: #475569; font-style: italic;">
                "{{ $post->excerpt }}"
            </div>
        @endif

        <div class="article-body prose" style="line-height: 1.8; font-size: 1.15rem; color: #1e293b;">
            {!! $post->content !!}
        </div>

        @if(!empty($post->faq_json))
            <hr class="my-5" />
            <div class="mb-5">
                <h3 class="mb-4" style="font-weight:700;"><i class="bi bi-question-circle text-primary"></i> Frequently Asked Questions</h3>
                <div class="accordion" id="faqAccordion">
                    @foreach($post->faq_json as $index => $faq)
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h4>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-5 pt-4 border-top text-center">
            <button onclick="window.close();" class="button secondary small">Close Preview Window</button>
        </div>
    </div>
@endsection

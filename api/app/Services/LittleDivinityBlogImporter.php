<?php

namespace App\Services;

use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogRevision;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class LittleDivinityBlogImporter
{
    private const SOURCE_BASE_URL = 'https://kanakshi.in';
    private const SOURCE_BLOG_INDEX = 'https://kanakshi.in/blogs/news';
    private const SOURCE_SITEMAP = 'https://kanakshi.in/sitemap_blogs_1.xml';

    /**
     * @return array{imported:int,updated:int,skipped:int,errors:array<int,string>,urls:array<int,string>}
     */
    public function import(bool $refresh = false): array
    {
        $blogMeta = $this->fetchBlogMeta();
        $articleUrls = $this->fetchArticleUrls();

        $result = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'urls' => $articleUrls,
        ];

        foreach ($articleUrls as $url) {
            try {
                $article = $this->fetchArticle($url);
                if (!$article) {
                    $result['errors'][] = "Could not parse article: {$url}";
                    continue;
                }

                $status = $this->importArticle($article, $blogMeta, $refresh);
                $result[$status]++;
            } catch (\Throwable $exception) {
                $result['errors'][] = "{$url}: {$exception->getMessage()}";
            }
        }

        return $result;
    }

    /**
     * @return array{name:string,description:string}
     */
    private function fetchBlogMeta(): array
    {
        $html = $this->fetchHtml(self::SOURCE_BLOG_INDEX);
        [$dom, $xpath] = $this->createDom($html);

        $name = $this->extractMetaContent($xpath, 'meta[property="og:title"]')
            ?: $this->extractTitleFromDocument($dom)
            ?: 'News';

        $name = trim(preg_replace('/\s+–\s+Kanakshi.in\s*$/u', '', $name) ?? $name);

        return [
            'name' => $name ?: 'News',
            'description' => $this->extractMetaContent($xpath, 'meta[property="og:description"]')
                ?: $this->extractMetaContent($xpath, 'meta[name="description"]')
                ?: 'Editorial updates, décor ideas, and brassware guides from Kanakshi.in.',
        ];
    }

    /**
     * @return array<int,string>
     */
    private function fetchArticleUrls(): array
    {
        $xml = $this->fetchXml(self::SOURCE_SITEMAP);
        $urls = [];

        foreach ($xml->url as $urlNode) {
            $loc = trim((string) $urlNode->loc);
            if (!$loc || $loc === self::SOURCE_BLOG_INDEX) {
                continue;
            }

            if (str_contains($loc, '/blogs/news/')) {
                $urls[] = $loc;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchArticle(string $url): ?array
    {
        $html = $this->fetchHtml($url);
        [$dom, $xpath] = $this->createDom($html);

        $jsonLd = $this->extractArticleJsonLd($xpath);
        $contentNode = $this->findFirstByClass($xpath, 'article-template__content');

        if (!$contentNode instanceof DOMElement) {
            return null;
        }

        $title = trim($this->extractNodeText($xpath, '//h1[contains(@class, "article-template__title")]'))
            ?: trim((string) ($jsonLd['headline'] ?? ''))
            ?: $this->extractMetaContent($xpath, 'meta[property="og:title"]');

        if (!$title) {
            return null;
        }

        $canonical = $this->extractAttribute($xpath, '//link[@rel="canonical"]', 'href') ?: $url;
        $description = trim((string) ($jsonLd['description'] ?? ''))
            ?: $this->extractMetaContent($xpath, 'meta[name="description"]')
            ?: $this->extractMetaContent($xpath, 'meta[property="og:description"]');

        $contentHtml = $this->normalizeArticleHtml($contentNode, $url);
        $contentText = trim(strip_tags(html_entity_decode($contentHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $excerpt = $description ?: Str::limit($contentText, 220, '');

        $authorName = trim((string) (($jsonLd['author']['name'] ?? $jsonLd['author'][0]['name'] ?? '')))
            ?: 'Kanakshi.in Editorial';

        $featureImage = $this->normalizeUrl(
            $this->extractMetaContent($xpath, 'meta[property="og:image:secure_url"]')
            ?: $this->extractMetaContent($xpath, 'meta[property="og:image"]')
            ?: (is_array($jsonLd['image'] ?? null) ? ($jsonLd['image'][0] ?? null) : ($jsonLd['image'] ?? null))
        );

        $publishedAt = !empty($jsonLd['datePublished']) ? Carbon::parse($jsonLd['datePublished']) : now();
        $updatedAt = !empty($jsonLd['dateModified']) ? Carbon::parse($jsonLd['dateModified']) : $publishedAt;

        $metaTitle = trim($this->extractTitleFromDocument($dom) ?: '') ?: $title;
        $metaTitle = trim(preg_replace('/\s+–\s+Kanakshi.in\s*$/u', '', $metaTitle) ?? $metaTitle);

        [$primaryKeyword, $secondaryKeywords] = $this->deriveKeywords($title, $description, $contentText);

        return [
            'source_url' => $url,
            'canonical_url' => $canonical,
            'title' => $title,
            'slug' => basename(parse_url($url, PHP_URL_PATH) ?: Str::slug($title)),
            'excerpt' => $excerpt,
            'content' => $contentHtml,
            'featured_image' => $featureImage,
            'featured_image_alt' => $title,
            'published_at' => $publishedAt,
            'updated_at' => $updatedAt,
            'meta_title' => $metaTitle,
            'meta_description' => $description ?: $excerpt,
            'og_title' => $this->extractMetaContent($xpath, 'meta[property="og:title"]') ?: $metaTitle,
            'og_description' => $this->extractMetaContent($xpath, 'meta[property="og:description"]') ?: ($description ?: $excerpt),
            'og_image' => $featureImage,
            'twitter_title' => $this->extractMetaContent($xpath, 'meta[name="twitter:title"]') ?: $metaTitle,
            'twitter_description' => $this->extractMetaContent($xpath, 'meta[name="twitter:description"]') ?: ($description ?: $excerpt),
            'twitter_image' => $featureImage,
            'author_name' => $authorName,
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => implode(', ', $secondaryKeywords),
            'tags' => $this->deriveTags($title, $description, $contentText),
            'reading_time' => $this->calculateReadingTime($contentText),
            'schema_type' => 'BlogPosting',
        ];
    }

    /**
     * @param array<string,mixed> $article
     * @param array{name:string,description:string} $blogMeta
     */
    private function importArticle(array $article, array $blogMeta, bool $refresh): string
    {
        $author = BlogAuthor::firstOrCreate(
            ['slug' => Str::slug($article['author_name'])],
            [
                'name' => $article['author_name'],
                'bio' => 'Editorial voice at Kanakshi.in, sharing insights on brassware, spiritual décor, and heritage-inspired living.',
                'avatar_alt' => $article['author_name'],
            ]
        );

        $category = BlogCategory::firstOrCreate(
            ['slug' => 'news'],
            [
                'name' => $blogMeta['name'],
                'description' => $blogMeta['description'],
                'meta_title' => $blogMeta['name'] . ' | Kanakshi.in',
                'meta_description' => $blogMeta['description'],
            ]
        );

        $post = BlogPost::where('slug', $article['slug'])->first();
        if ($post && !$refresh) {
            return 'skipped';
        }

        $adminId = User::query()->orderBy('id')->value('id');

        $payload = [
            'title' => $article['title'],
            'slug' => $article['slug'],
            'excerpt' => $article['excerpt'],
            'content' => $article['content'],
            'featured_image' => $article['featured_image'],
            'featured_image_alt' => $article['featured_image_alt'],
            'blog_author_id' => $author->id,
            'blog_category_id' => $category->id,
            'status' => 'published',
            'published_at' => $article['published_at'],
            'meta_title' => $article['meta_title'],
            'meta_description' => $article['meta_description'],
            'canonical_url' => null,
            'og_title' => $article['og_title'],
            'og_description' => $article['og_description'],
            'og_image' => $article['og_image'],
            'twitter_title' => $article['twitter_title'],
            'twitter_description' => $article['twitter_description'],
            'twitter_image' => $article['twitter_image'],
            'primary_keyword' => $article['primary_keyword'],
            'secondary_keywords' => $article['secondary_keywords'],
            'reading_time' => $article['reading_time'],
            'seo_noindex' => false,
            'seo_nofollow' => false,
            'schema_type' => $article['schema_type'],
            'faq_json' => [],
            'related_products_json' => [],
            'created_by' => $post?->created_by ?: $adminId,
            'updated_by' => $adminId,
            'last_updated_at' => $article['updated_at'],
        ];

        if ($post) {
            $post->update($payload);
            $status = 'updated';
        } else {
            $post = BlogPost::create($payload);
            $status = 'imported';
        }

        $tagIds = collect($article['tags'])
            ->filter()
            ->map(function (string $tagName) {
                return BlogTag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                )->id;
            })
            ->values()
            ->all();

        $post->tags()->sync($tagIds);

        BlogRevision::create([
            'blog_post_id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'faq_json' => $post->faq_json ?? [],
            'updated_by' => $adminId,
        ]);

        return $status;
    }

    private function fetchHtml(string $url): string
    {
        return Http::timeout(30)
            ->retry(2, 500)
            ->withHeaders([
                'User-Agent' => 'LittleDivinityBlogImporter/1.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])
            ->get($url)
            ->throw()
            ->body();
    }

    private function fetchXml(string $url): SimpleXMLElement
    {
        $body = Http::timeout(30)
            ->retry(2, 500)
            ->withHeaders([
                'User-Agent' => 'LittleDivinityBlogImporter/1.0',
                'Accept' => 'application/xml,text/xml;q=0.9,*/*;q=0.8',
            ])
            ->get($url)
            ->throw()
            ->body();

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_clear_errors();

        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException("Unable to parse XML from {$url}");
        }

        return $xml;
    }

    /**
     * @return array{0:DOMDocument,1:DOMXPath}
     */
    private function createDom(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return [$dom, new DOMXPath($dom)];
    }

    /**
     * @return array<string,mixed>
     */
    private function extractArticleJsonLd(DOMXPath $xpath): array
    {
        $nodes = $xpath->query('//script[@type="application/ld+json"]');
        if (!$nodes) {
            return [];
        }

        foreach ($nodes as $node) {
            $raw = trim($node->textContent ?? '');
            if ($raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $candidate = $this->findArticleInJsonLd($decoded);
            if ($candidate) {
                return $candidate;
            }
        }

        return [];
    }

    /**
     * @param array<string,mixed> $decoded
     * @return array<string,mixed>|null
     */
    private function findArticleInJsonLd(array $decoded): ?array
    {
        $type = $decoded['@type'] ?? null;
        if (is_string($type) && in_array($type, ['Article', 'BlogPosting', 'NewsArticle'], true)) {
            return $decoded;
        }

        foreach ($decoded as $value) {
            if (is_array($value)) {
                $result = $this->findArticleInJsonLd($value);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function findFirstByClass(DOMXPath $xpath, string $className): ?DOMElement
    {
        $nodes = $xpath->query(sprintf(
            '//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]',
            $className
        ));

        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function extractMetaContent(DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query('//' . $query);
        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);
        if (!$node instanceof DOMElement) {
            return null;
        }

        return trim((string) $node->getAttribute('content')) ?: null;
    }

    private function extractTitleFromDocument(DOMDocument $dom): ?string
    {
        $titles = $dom->getElementsByTagName('title');
        if ($titles->length === 0) {
            return null;
        }

        return trim((string) $titles->item(0)?->textContent) ?: null;
    }

    private function extractNodeText(DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);
        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        return trim((string) $nodes->item(0)?->textContent) ?: null;
    }

    private function extractAttribute(DOMXPath $xpath, string $query, string $attribute): ?string
    {
        $nodes = $xpath->query($query);
        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);
        if (!$node instanceof DOMElement) {
            return null;
        }

        return trim((string) $node->getAttribute($attribute)) ?: null;
    }

    private function normalizeArticleHtml(DOMElement $contentNode, string $articleUrl): string
    {
        $html = $this->innerHtml($contentNode);
        [$fragmentDom, $xpath] = $this->createDom('<div>' . $html . '</div>');

        foreach (['script', 'style', 'noscript'] as $tagName) {
            $nodes = $fragmentDom->getElementsByTagName($tagName);
            while ($nodes->length > 0) {
                $node = $nodes->item(0);
                $node?->parentNode?->removeChild($node);
            }
        }

        foreach ($xpath->query('//*[@href]') ?: [] as $node) {
            if ($node instanceof DOMElement) {
                $node->setAttribute('href', $this->normalizeUrl($node->getAttribute('href'), $articleUrl));
            }
        }

        foreach ($xpath->query('//*[@src]') ?: [] as $node) {
            if ($node instanceof DOMElement) {
                $node->setAttribute('src', $this->normalizeUrl($node->getAttribute('src'), $articleUrl));
            }
        }

        foreach ($xpath->query('//*') ?: [] as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $attributesToRemove = [];
            for ($index = 0; $index < $node->attributes->length; $index++) {
                $attribute = $node->attributes->item($index);
                if ($attribute && str_starts_with($attribute->nodeName, 'data-mce-')) {
                    $attributesToRemove[] = $attribute->nodeName;
                }
            }

            foreach ($attributesToRemove as $attributeName) {
                $node->removeAttribute($attributeName);
            }
        }

        $this->unwrapRedundantSpans($fragmentDom);
        $this->splitDenseParagraphs($fragmentDom);

        $root = $fragmentDom->getElementsByTagName('div')->item(0);
        $normalized = $root ? $this->innerHtml($root) : $html;
        $normalized = preg_replace('/<p>\s*[-—]{3,}\s*<\/p>/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/<br\s*\/?>\s*<\/p>/i', '</p>', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child);
        }

        return $html;
    }

    private function unwrapRedundantSpans(DOMDocument $dom): void
    {
        $spans = [];
        foreach ($dom->getElementsByTagName('span') as $span) {
            $spans[] = $span;
        }

        /** @var DOMElement $span */
        foreach ($spans as $span) {
            if ($span->attributes->length > 0 || !$span->parentNode) {
                continue;
            }

            while ($span->firstChild) {
                $span->parentNode->insertBefore($span->firstChild, $span);
            }

            $span->parentNode->removeChild($span);
        }
    }

    private function splitDenseParagraphs(DOMDocument $dom): void
    {
        $paragraphs = [];
        foreach ($dom->getElementsByTagName('p') as $paragraph) {
            $paragraphs[] = $paragraph;
        }

        /** @var DOMElement $paragraph */
        foreach ($paragraphs as $paragraph) {
            if (!$paragraph->parentNode) {
                continue;
            }

            $inner = trim($this->innerHtml($paragraph));
            if (!preg_match('/(?:<br\s*\/?>\s*){2,}/i', $inner)) {
                continue;
            }

            $segments = preg_split('/(?:<br\s*\/?>\s*){2,}/i', $inner) ?: [];
            $replacement = $dom->createDocumentFragment();
            $count = 0;

            foreach ($segments as $segment) {
                $segment = trim($segment);
                $segment = preg_replace('/^(?:<br\s*\/?>\s*)+/i', '', $segment) ?? $segment;
                $segment = trim($segment);

                if ($segment === '' || preg_match('/^[-—]{3,}$/u', strip_tags($segment))) {
                    continue;
                }

                $count++;
                $replacement->appendXML('<p>' . $segment . '</p>');
            }

            if ($count > 1) {
                $paragraph->parentNode->replaceChild($replacement, $paragraph);
            }
        }
    }

    /**
     * @return array{0:string,1:array<int,string>}
     */
    private function deriveKeywords(string $title, string $description, string $content): array
    {
        $haystack = Str::lower($title . ' ' . $description . ' ' . $content);
        $primary = 'brassware benefits';
        $secondary = [];

        if (str_contains($haystack, 'health')) {
            $secondary[] = 'health benefits of brassware';
        }

        if (str_contains($haystack, 'spiritual')) {
            $secondary[] = 'spiritual significance of brass';
        }

        if (str_contains($haystack, 'utensil')) {
            $secondary[] = 'brass utensils';
        }

        if (str_contains($haystack, 'decor')) {
            $secondary[] = 'brass home decor';
        }

        if (str_contains($haystack, 'kitchen')) {
            $secondary[] = 'traditional brass kitchenware';
        }

        if (empty($secondary)) {
            $secondary = [
                'brass home decor',
                'traditional brassware',
                'spiritual brass items',
            ];
        }

        return [$primary, array_values(array_unique($secondary))];
    }

    /**
     * @return array<int,string>
     */
    private function deriveTags(string $title, string $description, string $content): array
    {
        $haystack = Str::lower($title . ' ' . $description . ' ' . $content);
        $tags = ['Brassware'];

        $tagMap = [
            'health' => 'Health Benefits',
            'spiritual' => 'Spiritual Living',
            'decor' => 'Home Decor',
            'utensil' => 'Brass Utensils',
            'kitchen' => 'Kitchen Traditions',
            'heritage' => 'Indian Heritage',
            'ayurvedic' => 'Ayurveda',
        ];

        foreach ($tagMap as $needle => $label) {
            if (str_contains($haystack, $needle)) {
                $tags[] = $label;
            }
        }

        return array_values(array_unique($tags));
    }

    private function calculateReadingTime(string $text): int
    {
        $wordCount = str_word_count(strip_tags($text));
        return max(1, (int) ceil($wordCount / 220));
    }

    private function normalizeUrl(?string $url, ?string $base = null): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            return 'https:' . $url;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $base = $base ?: self::SOURCE_BASE_URL;

        if (Str::startsWith($url, '/')) {
            return rtrim(self::SOURCE_BASE_URL, '/') . $url;
        }

        return rtrim($base, '/') . '/' . ltrim($url, '/');
    }
}

<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\StoreSetting;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function rss(): Response
    {
        $store = StoreSetting::first();
        $siteName = $store?->site_name ?: 'Little Divinity';
        $siteTagline = $store?->site_tagline ?: 'Handcrafted Spiritual & E-Commerce Essentials';
        $customDomain = $this->normalizeBaseUrl($store?->custom_domain ?: config('app.frontend_url', 'http://localhost:3000'));
        $backendUrl = $this->normalizeBaseUrl(config('app.url', 'http://localhost'));
        $feedUrl = rtrim($backendUrl, '/') . '/api/v1/blog/feed/rss';
        
        $posts = BlogPost::with(['author', 'category'])
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rss .= '  <channel>' . "\n";
        $rss .= '    <title>' . htmlspecialchars($siteName) . '</title>' . "\n";
        $rss .= '    <link>' . htmlspecialchars($customDomain) . '</link>' . "\n";
        $rss .= '    <description>' . htmlspecialchars($siteTagline) . '</description>' . "\n";
        $rss .= '    <language>en-us</language>' . "\n";
        $rss .= '    <lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . "\n";
        $rss .= '    <atom:link href="' . htmlspecialchars($feedUrl) . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($posts as $post) {
            $postUrl = $customDomain . '/blog/' . $post->slug;
            $rss .= '    <item>' . "\n";
            $rss .= '      <title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $rss .= '      <link>' . htmlspecialchars($postUrl) . '</link>' . "\n";
            $rss .= '      <guid isPermaLink="true">' . htmlspecialchars($postUrl) . '</guid>' . "\n";
            $rss .= '      <description>' . htmlspecialchars($post->excerpt) . '</description>' . "\n";
            $rss .= '      <pubDate>' . $post->published_at->toRssString() . '</pubDate>' . "\n";
            
            if ($post->author) {
                $rss .= '      <author>' . htmlspecialchars($post->author->name) . '</author>' . "\n";
            }
            
            if ($post->category) {
                $rss .= '      <category>' . htmlspecialchars($post->category->name) . '</category>' . "\n";
            }

            if ($post->featured_image) {
                $rss .= '      <enclosure url="' . htmlspecialchars($this->resolveImageUrl($post->featured_image, $customDomain, $backendUrl)) . '" type="image/jpeg" />' . "\n";
            }
            
            $rss .= '    </item>' . "\n";
        }

        $rss .= '  </channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return response($rss, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function normalizeBaseUrl(string $url): string
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
        }

        return rtrim($url, '/');
    }

    private function resolveImageUrl(string $path, string $frontendBaseUrl, string $backendBaseUrl): string
    {
        if (preg_match("~^(?:f|ht)tps?://~i", $path)) {
            return $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return str_starts_with($path, '/')
                ? $backendBaseUrl . $path
                : $backendBaseUrl . '/' . $path;
        }

        return str_starts_with($path, '/')
            ? $frontendBaseUrl . $path
            : $frontendBaseUrl . '/' . $path;
    }
}

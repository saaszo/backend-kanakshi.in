<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AmazonProductLinkService
{
    /**
     * @return array{canonical_url:string, price:?float, fetched_at:\Illuminate\Support\Carbon}
     */
    public function fetchSnapshot(string $url): array
    {
        $canonicalUrl = $this->normalizeUrl($url);
        $html = $this->fetchProductHtml($canonicalUrl);
        $price = $this->extractPrice($html);

        return [
            'canonical_url' => $canonicalUrl,
            'price' => $price,
            'fetched_at' => now(),
        ];
    }

    public function normalizeUrl(string $url): string
    {
        $trimmed = trim($url);

        if (preg_match('~/(?:dp|gp/product)/([A-Z0-9]{10})~i', $trimmed, $match) === 1) {
            return 'https://www.amazon.in/dp/'.strtoupper($match[1]);
        }

        return $trimmed;
    }

    private function fetchProductHtml(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-IN,en;q=0.9',
        ])->timeout(8)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Amazon page fetch failed with status {$response->status()}.");
        }

        return $response->body();
    }

    private function extractPrice(string $html): ?float
    {
        if (preg_match('/One-time purchase:\s*₹\s*([\d,]+(?:\.\d{2})?)/u', $html, $match)) {
            return $this->parseMoney($match[1]);
        }

        if (preg_match('/<span class="a-offscreen">₹\s*([\d,]+(?:\.\d{2})?)<\/span>/u', $html, $match)) {
            return $this->parseMoney($match[1]);
        }

        if (preg_match('/priceToPay[^>]*>.*?<span[^>]*class="a-offscreen">₹\s*([\d,]+(?:\.\d{2})?)<\/span>/us', $html, $match)) {
            return $this->parseMoney($match[1]);
        }

        return null;
    }

    private function parseMoney(string $value): ?float
    {
        $normalized = preg_replace('/[^\d.]/', '', $value);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        return (float) $normalized;
    }
}

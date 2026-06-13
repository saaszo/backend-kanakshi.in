<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class AmazonSpreadsheetProductImporter
{
    public function __construct(private readonly AmazonProductLinkService $amazonProductLinkService) {}

    /**
     * @return array{created:int,updated:int,skipped:int,errors:array<int,string>,processed:int}
     */
    public function import(string $spreadsheetPath, bool $refresh = false, int $limit = 0): array
    {
        $rows = $this->readSpreadsheet($spreadsheetPath);
        $categories = Category::query()
            ->select('id', 'slug')
            ->get()
            ->pluck('id', 'slug');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;
        $errors = [];

        foreach ($rows as $row) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $processed++;

            $asin = trim((string) ($row['ASIN'] ?? ''));
            $name = trim((string) ($row['Product Name (Listed name may differ from Amazon API name; the count of products in this sheet may differ from the count in Brands)'] ?? ''));
            $productType = trim((string) ($row['Product Type'] ?? ''));
            $detailsPageLink = trim((string) ($row['Details Page Link'] ?? ''));

            if ($asin === '' || $name === '' || $detailsPageLink === '') {
                $skipped++;
                $errors[] = "Skipped row {$processed}: missing ASIN, product name, or details page link.";
                continue;
            }

            $sku = 'AMZ-' . $asin;
            $existing = Product::query()->where('sku', $sku)->first();

            if ($existing && ! $refresh) {
                $skipped++;
                continue;
            }

            $categorySlug = $this->guessCategorySlug($productType, $name);
            $categoryId = $categories[$categorySlug] ?? $categories['home-decor'] ?? null;

            if (! $categoryId) {
                $skipped++;
                $errors[] = "Skipped ASIN {$asin}: no category available for {$categorySlug}.";
                continue;
            }

            $scraped = [
                'sale_price' => null,
                'list_price' => null,
                'bullets' => [],
                'description' => '',
                'short_desc' => '',
                'images' => [],
            ];
            $images = [];
            $asinMatched = false;
            $canonicalAsin = null;
            $fetchError = null;

            try {
                $detailsPageLink = $this->amazonProductLinkService->normalizeUrl($detailsPageLink);
                $html = $this->fetchProductHtml($detailsPageLink);
                $canonicalAsin = $this->extractCanonicalAsin($html);
                $asinMatched = $canonicalAsin !== null && strtoupper($canonicalAsin) === strtoupper($asin);

                if ($asinMatched) {
                    $scraped = $this->extractAmazonData($html);
                    $images = $this->importImages($asin, $scraped['images']);
                }
            } catch (\Throwable $exception) {
                $fetchError = $exception->getMessage();
            }

            $bulletText = implode("\n", $scraped['bullets']);
            $shortDescription = $scraped['short_desc']
                ?: ($scraped['bullets'][0] ?? Str::limit($name, 180, ''));
            $description = $this->buildDescription($name, $scraped['description'], $scraped['bullets'], $detailsPageLink);

            [$price, $salePrice] = $this->resolvePricing(
                $scraped['list_price'],
                $scraped['sale_price']
            );

            $payload = [
                'category_id' => $categoryId,
                'name' => $name,
                'short_desc' => Str::limit(trim($shortDescription), 500, ''),
                'description' => $description,
                'bullet_points' => $bulletText !== '' ? $bulletText : null,
                'price' => $price,
                'sale_price' => $salePrice,
                'cost_price' => null,
                'weight' => null,
                'shipping_type' => 'default',
                'shipping_fee' => 0,
                'stock' => 20,
                'sku' => $sku,
                'images' => $images,
                'video_url' => null,
                'amazon_link' => $detailsPageLink,
                'amazon_button_enabled' => false,
                'amazon_price' => $scraped['sale_price'] ?? $scraped['list_price'],
                'amazon_price_fetched_at' => $asinMatched ? now() : null,
                'is_featured' => false,
                'is_active' => true,
                'is_sellable' => $price > 0 && $images !== [],
                'gst_percent' => 18.00,
                'meta_title' => Str::limit($name, 200, ''),
                'meta_desc' => Str::limit(trim($shortDescription), 320, ''),
            ];

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Product::query()->create($payload);
                $created++;
            }

            if ($fetchError) {
                $errors[] = "ASIN {$asin}: imported as coming soon because Amazon data could not be fetched ({$fetchError}).";
            } elseif (! $asinMatched) {
                $found = $canonicalAsin ?: 'unknown';
                $errors[] = "ASIN {$asin}: Amazon returned canonical ASIN {$found}, so the product was imported without scraped price/images.";
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'processed' => $processed,
        ];
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function readSpreadsheet(string $spreadsheetPath): array
    {
        if (! is_file($spreadsheetPath)) {
            throw new \RuntimeException("Spreadsheet not found: {$spreadsheetPath}");
        }

        if (Str::endsWith(Str::lower($spreadsheetPath), '.json')) {
            $decoded = json_decode((string) file_get_contents($spreadsheetPath), true);

            if (! is_array($decoded)) {
                throw new \RuntimeException("Invalid JSON import file: {$spreadsheetPath}");
            }

            return array_values(array_filter(
                $decoded,
                static fn ($row): bool => is_array($row)
            ));
        }

        $zip = new ZipArchive();
        if ($zip->open($spreadsheetPath) !== true) {
            throw new \RuntimeException("Unable to open spreadsheet: {$spreadsheetPath}");
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml') ?: '';
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml') ?: '';
        $zip->close();

        if ($sheetXml === '') {
            throw new \RuntimeException('Sheet data not found in spreadsheet.');
        }

        $sharedStrings = [];
        if ($sharedStringsXml !== '') {
            $sharedDocument = simplexml_load_string($sharedStringsXml);
            if ($sharedDocument !== false) {
                foreach ($sharedDocument->si as $item) {
                    if (isset($item->t)) {
                        $sharedStrings[] = (string) $item->t;
                        continue;
                    }

                    $parts = [];
                    foreach ($item->r as $run) {
                        $parts[] = (string) $run->t;
                    }
                    $sharedStrings[] = implode('', $parts);
                }
            }
        }

        $document = simplexml_load_string($sheetXml);
        if ($document === false || ! isset($document->sheetData)) {
            throw new \RuntimeException('Unable to parse sheet XML.');
        }

        $rows = [];
        foreach ($document->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnLetters = preg_replace('/\d+/', '', $reference) ?: '';
                $columnIndex = $this->columnLettersToIndex($columnLetters);
                $cellType = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';

                if ($cellType === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($cellType === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }

                $cells[$columnIndex] = trim($value);
            }

            if ($cells !== []) {
                ksort($cells);
                $rows[] = $cells;
            }
        }

        if ($rows === []) {
            return [];
        }

        $headers = array_values(array_shift($rows));
        $mapped = [];

        foreach ($rows as $row) {
            $record = [];
            foreach ($headers as $index => $header) {
                $record[$header] = trim((string) ($row[$index] ?? ''));
            }
            $mapped[] = $record;
        }

        return $mapped;
    }

    private function columnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
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

    private function extractCanonicalAsin(string $html): ?string
    {
        if (preg_match('/<link rel="canonical" href="https:\/\/www\.amazon\.in\/[^"]+\/dp\/([A-Z0-9]{10})"/i', $html, $match)) {
            return strtoupper($match[1]);
        }

        return null;
    }

    /**
     * @return array{sale_price:?float,list_price:?float,bullets:array<int,string>,description:string,short_desc:string,images:array<int,string>}
     */
    private function extractAmazonData(string $html): array
    {
        $bullets = [];
        if (preg_match('/id="pqv-feature-bullets".*?<ul[^>]*>(.*?)<\/ul>/s', $html, $match)) {
            preg_match_all('/<li><span class="a-list-item">(.*?)<\/span><\/li>/s', $match[1], $bulletMatches);
            $bullets = array_values(array_filter(array_map(
                fn (string $item): string => $this->cleanText($item),
                $bulletMatches[1] ?? []
            )));
        }

        $description = '';
        if (preg_match('/id="pqv-description".*?<div[^>]*>\s*<p>(.*?)<\/p>/s', $html, $match)) {
            $description = $this->cleanText($match[1]);
        }

        $salePrice = null;
        if (preg_match('/One-time purchase:\s*₹\s*([\d,]+(?:\.\d{2})?)/u', $html, $match)) {
            $salePrice = $this->parseMoney($match[1]);
        } elseif (preg_match('/<span class="a-offscreen">₹\s*([\d,]+(?:\.\d{2})?)<\/span>/u', $html, $match)) {
            $salePrice = $this->parseMoney($match[1]);
        }

        $listPrice = null;
        if (preg_match('/List Price:\s*<span class="a-text-strike">\s*₹\s*([\d,]+(?:\.\d{2})?)\s*<\/span>/u', $html, $match)) {
            $listPrice = $this->parseMoney($match[1]);
        } elseif (preg_match('/M\.R\.P:.*?₹\s*([\d,]+(?:\.\d{2})?)/us', $html, $match)) {
            $listPrice = $this->parseMoney($match[1]);
        }

        preg_match_all('/data-old-hires="(https:\/\/m\.media-amazon\.com\/images\/I\/[^"]+)"/', $html, $imageMatches);
        $images = array_values(array_slice(array_unique($imageMatches[1] ?? []), 0, 8));

        return [
            'sale_price' => $salePrice,
            'list_price' => $listPrice,
            'bullets' => $bullets,
            'description' => $description,
            'short_desc' => $bullets[0] ?? '',
            'images' => $images,
        ];
    }

    /**
     * @param  array<int,string>  $remoteUrls
     * @return array<int,string>
     */
    private function importImages(string $asin, array $remoteUrls): array
    {
        $imported = [];
        $disk = Storage::disk('public');

        foreach ($remoteUrls as $index => $remoteUrl) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                ])->timeout(30)->get($remoteUrl);

                if (! $response->successful()) {
                    continue;
                }

                $binary = $response->body();
                if ($binary === '') {
                    continue;
                }

                $extension = $this->guessImageExtension(
                    $response->header('Content-Type'),
                    $remoteUrl
                );

                $path = "products/amazon-imported/{$asin}/" . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . ".{$extension}";
                $disk->put($path, $binary);
                $imported[] = 'storage/' . $path;
            } catch (\Throwable) {
                continue;
            }
        }

        return $imported;
    }

    private function guessImageExtension(?string $contentType, string $url): string
    {
        $contentType = strtolower((string) $contentType);

        return match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            preg_match('/\.(png|webp|gif)(?:\?|$)/i', $url, $match) === 1 => strtolower($match[1]),
            default => 'jpg',
        };
    }

    private function guessCategorySlug(string $productType, string $name): string
    {
        $productType = Str::upper($productType);
        $name = Str::lower($name);

        if (str_contains($name, 'wall') || str_contains($name, 'hanging') || str_contains($name, 'portrait') || str_contains($name, 'panel')) {
            return 'wall-decor';
        }

        if (str_contains($name, 'table') || str_contains($name, 'pen stand') || str_contains($name, 'candle stand')) {
            return 'table-decor';
        }

        if (
            str_contains($name, 'fork')
            || str_contains($name, 'spoon')
            || str_contains($name, 'tray')
            || str_contains($name, 'bowl')
            || str_contains($name, 'plate')
            || str_contains($name, 'glass')
            || $productType === 'FLATWARE'
        ) {
            return 'home-kitchen';
        }

        if (
            str_contains($name, 'diya')
            || str_contains($name, 'deepak')
            || str_contains($name, 'lamp')
            || str_contains($name, 'kalash')
            || str_contains($name, 'lota')
            || str_contains($name, 'mandir')
            || str_contains($name, 'pooja')
            || $productType === 'FUEL LAMP'
        ) {
            return 'pooja-decor';
        }

        if (
            str_contains($name, 'idol')
            || str_contains($name, 'murti')
            || str_contains($name, 'statue')
            || str_contains($name, 'shivling')
            || str_contains($name, 'nandi')
            || str_contains($name, 'hanuman')
            || str_contains($name, 'ganesh')
            || str_contains($name, 'ganesha')
            || str_contains($name, 'krishna')
            || str_contains($name, 'ram')
            || str_contains($name, 'balaji')
            || str_contains($name, 'lakshmi')
            || str_contains($name, 'shiv')
            || $productType === 'FIGURINE'
        ) {
            return 'god-idols';
        }

        if ($productType === 'HOME FURNITURE AND DECOR') {
            return 'home-decor';
        }

        return 'home-decor';
    }

    /**
     * @return array{0:float,1:?float}
     */
    private function resolvePricing(?float $listPrice, ?float $salePrice): array
    {
        if ($salePrice !== null && $listPrice !== null && $listPrice > $salePrice) {
            return [$listPrice, $salePrice];
        }

        if ($salePrice !== null) {
            return [$salePrice, null];
        }

        if ($listPrice !== null) {
            return [$listPrice, null];
        }

        return [0.0, null];
    }

    /**
     * @param  array<int,string>  $bullets
     */
    private function buildDescription(string $name, string $description, array $bullets, string $sourceUrl): string
    {
        $parts = [];

        if ($description !== '') {
            $parts[] = '<p>' . e($description) . '</p>';
        } else {
            $parts[] = '<p>' . e($name) . '</p>';
        }

        if ($bullets !== []) {
            $parts[] = '<ul>' . implode('', array_map(
                static fn (string $bullet): string => '<li>' . e($bullet) . '</li>',
                $bullets
            )) . '</ul>';
        }

        $parts[] = '<p>Imported from Amazon listing source: <a href="' . e($sourceUrl) . '" target="_blank" rel="noopener noreferrer">' . e($sourceUrl) . '</a>.</p>';

        return implode("\n", $parts);
    }

    private function cleanText(string $html): string
    {
        $text = strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?: '';

        return trim($text);
    }

    private function parseMoney(string $value): ?float
    {
        $normalized = str_replace([',', '₹', ' '], '', $value);

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}

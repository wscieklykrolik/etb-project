<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class InPostPublicPriceService
{
    public const URL = 'https://inpost.pl/cenniki';

    private const CACHE_KEY = 'shop:inpost:retail:locker:b:v1';

    public function quote(bool $refresh = false): array
    {
        if (! $refresh && ($cached = Cache::get(self::CACHE_KEY)) !== null) {
            return $cached;
        }
        try {
            $response = Http::connectTimeout(2)->timeout(5)->withOptions(['allow_redirects' => false])->get(self::URL)->throw();
            $quote = ['price_grosze' => $this->parse($response->body()), 'checked_at' => now()->toIso8601String()];
            Cache::put(self::CACHE_KEY, $quote, now()->addHours(6));

            return $quote;
        } catch (Throwable $exception) {
            // Nie zastępuj brakującego cennika zmyśloną albo przeterminowaną ceną.
            $quote = ['price_grosze' => null, 'checked_at' => null];
            Cache::put(self::CACHE_KEY, $quote, now()->addMinute());

            return $quote;
        }
    }

    public function parse(string $html): int
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $tables = $xpath->query('//h2[normalize-space(.)="InPost Szybkie Nadania Paczkomat"]/following::table[1]');
        $prices = [];
        foreach ($tables as $table) {
            foreach ($xpath->query('.//tr', $table) as $row) {
                $cells = $xpath->query('./td', $row);
                if ($cells->length < 3 || trim($cells->item(0)->textContent) !== 'Gabaryt B') {
                    continue;
                }
                $price = preg_replace('/\s+/u', ' ', trim($cells->item(1)->textContent));
                if (preg_match('/^(\d{1,3}),(\d{2}) zł$/u', $price, $matches)) {
                    $prices[] = (int) $matches[1] * 100 + (int) $matches[2];
                }
            }
        }
        if (count($prices) !== 1 || $prices[0] <= 0) {
            throw new RuntimeException('Nie można jednoznacznie odczytać ceny gabarytu B z cennika InPost.');
        }

        return $prices[0];
    }
}

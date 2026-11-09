<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class TranslationService
{
    private string $url;
    private ?string $apiKey;
    private bool $mockMode;

    public function __construct()
    {
        $this->url = rtrim(config('services.translation.url', 'https://translate.kenantomfie.com'), '/');
        $this->apiKey = config('services.translation.api_key');
        $this->mockMode = (bool) config('services.translation.mock_mode', false);
    }

    /**
     * Translate a single text string from source to target locale.
     */
    public function translate(?string $text, string $target = 'en', string $source = 'id'): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        if ($source === $target) {
            return $text;
        }

        if ($this->mockMode) {
            return $this->mockTranslate($text, $target);
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders(array_filter(['X-API-Key' => $this->apiKey]))
                ->asJson()
                ->post("{$this->url}/translate", array_filter([
                    'q' => $text,
                    'source' => $source,
                    'target' => $target,
                    'format' => 'text',
                    'api_key' => $this->apiKey,
                ]));

            if ($response->successful()) {
                $translated = $response->json('translatedText');
                if (is_string($translated) && $translated !== '') {
                    return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }

            AppLogger::warning('translation', 'libretranslate.api_error', 'LibreTranslate API mengembalikan status non-sukses.', [
                'status' => $response->status(),
                'response' => $response->json(),
                'sample' => mb_substr($text, 0, 80),
            ]);
        } catch (Throwable $e) {
            AppLogger::warning('translation', 'libretranslate.exception', 'Gagal memanggil server LibreTranslate: ' . $e->getMessage(), [
                'url' => $this->url,
                'sample' => mb_substr($text, 0, 80),
            ]);
        }

        // Graceful fallback to avoid breaking partner/user workflow
        return $this->mockTranslate($text, $target);
    }

    /**
     * Translate multiple texts in a single batch request.
     *
     * @param array<string, string> $texts Key-value pair of fields to translate
     * @return array<string, string>
     */
    public function translateBatch(array $texts, string $target = 'en', string $source = 'id'): array
    {
        $keys = array_keys($texts);
        $values = array_values($texts);
        $results = [];

        if (empty($values) || $source === $target) {
            return $texts;
        }

        if ($this->mockMode) {
            foreach ($texts as $k => $v) {
                $results[$k] = $this->mockTranslate($v, $target);
            }
            return $results;
        }

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders(array_filter(['X-API-Key' => $this->apiKey]))
                ->asJson()
                ->post("{$this->url}/translate", array_filter([
                    'q' => $values,
                    'source' => $source,
                    'target' => $target,
                    'format' => 'text',
                    'api_key' => $this->apiKey,
                ]));

            if ($response->successful()) {
                $translations = $response->json('translatedText');
                if (is_array($translations)) {
                    foreach ($keys as $idx => $key) {
                        $raw = $translations[$idx] ?? null;
                        $results[$key] = is_string($raw) && $raw !== ''
                            ? html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                            : $this->mockTranslate($values[$idx] ?? '', $target);
                    }
                    return $results;
                }
            }
        } catch (Throwable $e) {
            AppLogger::warning('translation', 'libretranslate.batch_exception', 'Gagal batch translate: ' . $e->getMessage(), [
                'url' => $this->url,
            ]);
        }

        foreach ($texts as $k => $v) {
            $results[$k] = $this->mockTranslate($v, $target);
        }

        return $results;
    }

    /**
     * Fallback dictionary translation for common regional travel terms.
     */
    public function mockTranslate(string $text, string $target = 'en'): string
    {
        if ($target === 'id') {
            return $text;
        }

        $dictionary = [
            'Danau Toba' => 'Lake Toba',
            'Paket Tour' => 'Tour Package',
            'Sewa Mobil' => 'Car Rental',
            'Termasuk' => 'Included',
            'Tidak Termasuk' => 'Excluded',
            'Hari' => 'Day',
            'Malam' => 'Night',
            'Pulau Samosir' => 'Samosir Island',
            'Bukit Lawang' => 'Bukit Lawang',
            'Tangkahan' => 'Tangkahan',
            'Berastagi' => 'Berastagi',
            'Wisata' => 'Tour',
            'Perjalanan' => 'Trip',
        ];

        return strtr($text, $dictionary);
    }
}

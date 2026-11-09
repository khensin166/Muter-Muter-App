<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleTranslationService
{
    private ?string $apiKey;
    private bool $mockMode;
    private string $endpoint;
    private ?string $libretranslateUrl;
    private ?string $libretranslateApiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_translate.api_key');
        $this->mockMode = (bool) config('services.google_translate.mock_mode', false);
        $this->endpoint = config('services.google_translate.endpoint', 'https://translation.googleapis.com/language/translate/v2');
        $this->libretranslateUrl = config('services.libretranslate.url');
        $this->libretranslateApiKey = config('services.libretranslate.api_key');
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

        // 1. Try LibreTranslate (Self-Hosted) if configured
        if (! empty($this->libretranslateUrl)) {
            try {
                $response = Http::timeout(8)
                    ->withoutVerifying()
                    ->withHeaders(array_filter(['X-API-Key' => $this->libretranslateApiKey]))
                    ->asJson()
                    ->post(rtrim($this->libretranslateUrl, '/').'/translate', array_filter([
                        'q' => $text,
                        'source' => $source,
                        'target' => $target,
                        'format' => 'text',
                        'api_key' => $this->libretranslateApiKey,
                    ]));

                if ($response->successful()) {
                    $translated = $response->json('translatedText');
                    if (is_string($translated) && $translated !== '') {
                        return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
            } catch (Throwable $e) {
                AppLogger::warning('translation', 'libretranslate.exception', 'Gagal memanggil LibreTranslate: '.$e->getMessage(), [
                    'url' => $this->libretranslateUrl,
                    'sample' => mb_substr($text, 0, 80),
                ]);
            }
        }

        if ($this->mockMode || empty($this->apiKey)) {
            return $this->mockTranslate($text, $target);
        }

        // 2. Try Google Translate API as fallback
        try {
            $response = Http::timeout(8)
                ->withoutVerifying()
                ->asJson()
                ->post("{$this->endpoint}?key={$this->apiKey}", [
                    'q' => [$text],
                    'target' => $target,
                    'source' => $source,
                    'format' => 'text',
                ]);

            if ($response->successful()) {
                $translatedText = $response->json('data.translations.0.translatedText');
                if (is_string($translatedText) && $translatedText !== '') {
                    return html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }

            AppLogger::warning('translation', 'google.translate.api_error', 'Google Translate API mengembalikan respon non-sukses.', [
                'status' => $response->status(),
                'response' => $response->json(),
                'sample' => mb_substr($text, 0, 80),
            ]);
        } catch (Throwable $e) {
            AppLogger::warning('translation', 'google.translate.exception', 'Gagal memanggil Google Translate API: ' . $e->getMessage(), [
                'sample' => mb_substr($text, 0, 80),
            ]);
        }

        // 3. Graceful fallback to avoid breaking user workflow
        return $this->mockTranslate($text, $target);
    }

    /**
     * Translate multiple texts in a single batch request to conserve quota and minimize latency.
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

        // 1. Try LibreTranslate (Self-Hosted) Batch if configured
        if (! empty($this->libretranslateUrl)) {
            try {
                $response = Http::timeout(15)
                    ->withoutVerifying()
                    ->withHeaders(array_filter(['X-API-Key' => $this->libretranslateApiKey]))
                    ->asJson()
                    ->post(rtrim($this->libretranslateUrl, '/').'/translate', array_filter([
                        'q' => $values,
                        'source' => $source,
                        'target' => $target,
                        'format' => 'text',
                        'api_key' => $this->libretranslateApiKey,
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
                AppLogger::warning('translation', 'libretranslate.batch_exception', 'Gagal memanggil LibreTranslate batch: '.$e->getMessage(), [
                    'url' => $this->libretranslateUrl,
                ]);
            }
        }

        if ($this->mockMode || empty($this->apiKey)) {
            foreach ($texts as $k => $v) {
                $results[$k] = $this->mockTranslate($v, $target);
            }
            return $results;
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->asJson()
                ->post("{$this->endpoint}?key={$this->apiKey}", [
                    'q' => $values,
                    'target' => $target,
                    'source' => $source,
                    'format' => 'text',
                ]);

            if ($response->successful()) {
                $translations = $response->json('data.translations') ?? [];
                foreach ($keys as $idx => $key) {
                    $raw = $translations[$idx]['translatedText'] ?? null;
                    $results[$key] = is_string($raw) && $raw !== ''
                        ? html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                        : $this->mockTranslate($values[$idx] ?? '', $target);
                }
                return $results;
            }
        } catch (Throwable $e) {
            AppLogger::warning('translation', 'google.translate.batch_exception', $e->getMessage());
        }

        foreach ($texts as $k => $v) {
            $results[$k] = $this->mockTranslate($v, $target);
        }

        return $results;
    }

    /**
     * Contextual mock translation fallback when API quota is exceeded, offline, or in test mode.
     */
    private function mockTranslate(string $text, string $target): string
    {
        if ($target !== 'en') {
            return $text;
        }

        // Common domain dictionary for North Sumatra travel terms
        $commonPhrases = [
            'Paket Wisata' => 'Tour Package',
            'Rental Mobil' => 'Car Rental',
            'Sewa Mobil' => 'Car Rental',
            'Danau Toba' => 'Lake Toba',
            'Bukit Lawang' => 'Bukit Lawang',
            'Tangkahan' => 'Tangkahan Eco-Park',
            'Berastagi' => 'Berastagi Highland',
            'Medan' => 'Medan Hub',
            'Tiket kapal feri' => 'Ferry crossing tickets',
            'Pemandu lokal' => 'Certified local guide',
            'Makan siang' => 'Lunch',
            'Penjemputan bandara' => 'Airport pick-up service',
            'Pengeluaran pribadi' => 'Personal expenses',
            'Air Terjun' => 'Waterfall',
            'Hari' => 'Day',
        ];

        $translated = $text;
        foreach ($commonPhrases as $idPhrase => $enPhrase) {
            $translated = preg_replace('/\b' . preg_quote($idPhrase, '/') . '\b/u', $enPhrase, $translated);
        }

        return $translated;
    }
}


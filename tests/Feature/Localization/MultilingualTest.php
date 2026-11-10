<?php

use App\Models\Destination;
use App\Models\Product;
use App\Models\TourPackage;
use App\Services\TranslationService;

test('locale switcher endpoint updates session and sets cookie', function () {
    $response = $this->post('/locale', ['locale' => 'en']);

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('locale', 'en');
});

test('locale switcher endpoint rejects unsupported languages', function () {
    $response = $this->postJson('/locale', ['locale' => 'fr']);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['locale']);
});

test('has translations trait resolves active locale dynamically', function () {
    $product = new Product();
    $product->name = [
        'id' => 'Tur Danau Toba 3 Hari',
        'en' => 'Lake Toba 3 Days Tour',
    ];

    app()->setLocale('id');
    expect($product->name)->toBe('Tur Danau Toba 3 Hari');

    app()->setLocale('en');
    expect($product->name)->toBe('Lake Toba 3 Days Tour');
});

test('has translations trait falls back to default language when target language is empty', function () {
    $product = new Product();
    $product->name = [
        'id' => 'Pakej Asli Danau Toba',
        'en' => null,
    ];

    app()->setLocale('en');
    expect($product->name)->toBe('Pakej Asli Danau Toba');
});

test('translation batch api translates key-value pairs', function () {
    $payload = [
        'fields' => [
            'title' => 'Wisata Danau Toba',
            'desc' => 'Paket liburan keluarga di Samosir',
        ],
        'source' => 'id',
        'target' => 'en',
    ];

    $response = $this->postJson('/api/translate-batch', $payload);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'translations' => ['title', 'desc'],
    ]);

    expect($response->json('translations.title'))->not->toBeEmpty();
});

test('single translation api endpoint returns translated string', function () {
    $response = $this->postJson('/api/translate', [
        'text' => 'Danau Toba',
        'source' => 'id',
        'target' => 'en',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
    expect($response->json('translated'))->toContain('Toba');
});

test('static translation json dictionaries exist and contain matching keys', function () {
    $idPath = lang_path('id.json');
    $enPath = lang_path('en.json');

    expect(file_exists($idPath))->toBeTrue();
    expect(file_exists($enPath))->toBeTrue();

    $idDict = json_decode(file_get_contents($idPath), true);
    $enDict = json_decode(file_get_contents($enPath), true);

    expect($idDict)->toBeArray()->not->toBeEmpty();
    expect($enDict)->toBeArray()->not->toBeEmpty();
    expect($idDict['nav.tour_packages'])->toBe('Paket Wisata');
    expect($enDict['nav.tour_packages'])->toBe('Tour Packages');
});

test('homepage returns english translations and cluster labels when locale is en', function () {
    $response = $this->withSession(['locale' => 'en'])->get('/');

    $response->assertOk();
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->component('Welcome')
        ->where('locale', 'en')
        ->has('translations')
        ->where('translations', fn ($translations) => 
            isset($translations['nav.explore_destinations']) &&
            $translations['nav.explore_destinations'] === 'Explore Destinations' &&
            isset($translations['home.hero_badge']) &&
            $translations['home.hero_badge'] === 'North Sumatra Integrated Travel Marketplace'
        )
    );
});

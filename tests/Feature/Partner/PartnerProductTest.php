<?php

namespace Tests\Feature\Partner;

use App\Enums\ApprovalStatus;
use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->partner = Partner::create([
        'business_name' => 'CV Lake Toba Explorer',
        'pic_name' => 'Bona',
        'pic_phone' => '08123456789',
        'email' => 'bona@explorer.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Parapat',
    ]);

    $this->owner = User::create([
        'name' => 'Bona Pasaribu',
        'email' => 'bona@explorer.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    PartnerMember::create([
        'partner_id' => $this->partner->id,
        'user_id' => $this->owner->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);
});

test('partner can view their products list', function () {
    $response = $this->actingAs($this->owner)->get(route('partner.products.index'));

    $response->assertStatus(200);
});

test('partner can render create tour page', function () {
    $response = $this->actingAs($this->owner)->get(route('partner.tours.create'));

    $response->assertStatus(200);
});

test('partner can create tour package with itinerary and ferry inclusion', function () {
    $response = $this->actingAs($this->owner)->post(route('partner.tours.store'), [
        'name' => 'Tour 2D1N Pulau Samosir Heritage',
        'short_description' => 'Paket hemat keliling Samosir',
        'full_description' => 'Deskripsi lengkap',
        'tour_type' => 'PRIVATE_TRIP',
        'base_price' => 850000,
        'pricing_type' => 'PER_PERSON',
        'duration_days' => 2,
        'duration_nights' => 1,
        'min_participants' => 2,
        'max_participants' => 8,
        'includes_ferry' => true,
        'inclusions' => 'Tiket ferry, mobil, supir',
        'exclusions' => 'Pengeluaran pribadi',
        'terms_conditions' => 'DP 30%',
        'itinerary' => [
            [
                'day_number' => 1,
                'title' => 'Menyeberang ke Tomok',
                'description' => 'Naik kapal ferry dari Parapat',
                'stops' => [
                    [
                        'title' => 'Makam Raja Sidabutar',
                        'activity_description' => 'Wisata sejarah',
                        'estimated_minutes' => 60,
                    ],
                ],
            ],
        ],
    ]);

    $response->assertRedirect(route('partner.products.index'));

    $product = Product::where('partner_id', $this->partner->id)->first();
    expect($product)->not->toBeNull();
    expect($product->approval_status)->toBe(ApprovalStatus::PENDING_REVIEW);
    expect($product->min_lead_time_hours)->toBe(24);
    expect($product->tourPackage->includes_ferry)->toBeTrue();
    expect($product->tourPackage->itineraryDays)->toHaveCount(1);
});

test('partner can render create vehicle page', function () {
    $response = $this->actingAs($this->owner)->get(route('partner.vehicles.create'));

    $response->assertStatus(200);
});

test('partner can create vehicle rental unit', function () {
    $response = $this->actingAs($this->owner)->post(route('partner.vehicles.store'), [
        'name' => 'Toyota HiAce 14 Penumpang',
        'short_description' => 'Minibus luas untuk keluarga',
        'full_description' => 'AC dingin dan suspensi nyaman',
        'vehicle_type' => 'MINIBUS',
        'brand' => 'Toyota',
        'model' => 'HiAce Commuter',
        'manufacturing_year' => 2022,
        'passenger_capacity' => 14,
        'luggage_capacity' => 6,
        'transmission' => 'MANUAL',
        'with_driver' => true,
        'license_plate' => 'BK 9999 TO',
        'base_price' => 1200000,
        'pricing_type' => 'PER_VEHICLE',
    ]);

    $response->assertRedirect(route('partner.products.index'));

    $product = Product::where('name', 'Toyota HiAce 14 Penumpang')->first();
    expect($product)->not->toBeNull();
    expect($product->approval_status)->toBe(ApprovalStatus::PENDING_REVIEW);
    expect($product->min_lead_time_hours)->toBe(12);
    expect($product->vehicle->passenger_capacity)->toBe(14);
});

test('partner can toggle product active status', function () {
    $product = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => \App\Enums\OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Tour Test',
        'slug' => 'tour-test',
        'short_description' => 'Test',
        'full_description' => 'Test',
        'base_price' => 100000,
        'pricing_type' => \App\Enums\PricingType::PER_PERSON,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 24,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::APPROVED,
    ]);

    $response = $this->actingAs($this->owner)->patch(route('partner.products.toggle', $product->id));

    $response->assertSessionHas('success');
    expect($product->fresh()->status)->toBe(ProductStatus::ARCHIVED);
});

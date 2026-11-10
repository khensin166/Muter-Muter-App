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

test('partner cannot access tour creation as tours are curated by super admin', function () {
    expect(route('partner.tours.create', [], false))->toBeNull();
})->throws(\Symfony\Component\Routing\Exception\RouteNotFoundException::class);

test('partner can render create vehicle page', function () {
    $response = $this->actingAs($this->owner)->get(route('partner.vehicles.create'));

    $response->assertStatus(200);
});

test('partner can create vehicle rental unit with centralized platform pricing', function () {
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
    ]);

    $response->assertRedirect(route('partner.products.index'));

    $product = Product::where('partner_id', $this->partner->id)->first();
    expect($product)->not->toBeNull();
    expect($product->getTranslation('name', 'id'))->toBe('Toyota HiAce 14 Penumpang');
    expect((float) $product->base_price)->toBe(1350000.0);
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

<?php

namespace Tests\Feature\Catalog;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use App\Models\Partner;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\Vehicle;

beforeEach(function () {
    $this->partner = Partner::create([
        'business_name' => 'Horas Tour & Transport',
        'pic_name' => 'Tigor',
        'pic_phone' => '0812345678',
        'email' => 'tigor@horas.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
    ]);

    // Published Tour
    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Paket Tour 3D2N Samosir',
        'slug' => 'paket-tour-3d2n-samosir',
        'short_description' => 'Eksplorasi Danau Toba 3 hari 2 malam',
        'full_description' => 'Detail itinerary dan kelengkapan tur.',
        'base_price' => 1200000,
        'pricing_type' => PricingType::PER_PERSON,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 24,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::APPROVED,
    ]);

    TourPackage::create([
        'product_id' => $this->tourProduct->id,
        'tour_type' => TourType::PRIVATE_TRIP,
        'duration_days' => 3,
        'duration_nights' => 2,
        'min_participants' => 2,
        'max_participants' => 10,
        'includes_ferry' => true,
    ]);

    // Unapproved Tour (Pending)
    $this->pendingTour = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Paket Rahasia Belum Disetujui',
        'slug' => 'paket-rahasia-belum-disetujui',
        'short_description' => 'Tidak boleh tampil publik',
        'full_description' => 'Deskripsi',
        'base_price' => 500000,
        'pricing_type' => PricingType::PER_PERSON,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 24,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::PENDING_REVIEW,
    ]);

    // Published Vehicle
    $this->vehicleProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::VEHICLE,
        'name' => 'Sewa Toyota Avanza + Driver',
        'slug' => 'sewa-toyota-avanza-driver',
        'short_description' => 'Mobil MPV bersih ber-AC',
        'full_description' => 'Sewa termasuk supir dan bbm',
        'base_price' => 650000,
        'pricing_type' => PricingType::PER_VEHICLE,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 12,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::APPROVED,
    ]);

    Vehicle::create([
        'product_id' => $this->vehicleProduct->id,
        'vehicle_type' => VehicleType::CAR,
        'brand' => 'Toyota',
        'model' => 'Avanza',
        'manufacturing_year' => 2021,
        'passenger_capacity' => 6,
        'luggage_capacity' => 3,
        'transmission' => TransmissionType::MANUAL,
        'with_driver' => true,
        'license_plate' => 'BK 1234 AB',
    ]);
});

test('public can view tours listing and only see approved products', function () {
    $response = $this->get(route('tours.index'));

    $response->assertStatus(200);
});

test('public can view single tour detail', function () {
    $response = $this->get(route('tours.show', $this->tourProduct->slug));

    $response->assertStatus(200);
});

test('unapproved tour returns 404 in public detail view', function () {
    $response = $this->get(route('tours.show', $this->pendingTour->slug));

    $response->assertStatus(404);
});

test('public can view vehicles listing', function () {
    $response = $this->get(route('vehicles.index'));

    $response->assertStatus(200);
});

test('public can view single vehicle detail', function () {
    $response = $this->get(route('vehicles.show', $this->vehicleProduct->slug));

    $response->assertStatus(200);
});

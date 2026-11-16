<?php

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingVehicleDetail;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->customer = User::factory()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $this->partnerUser1 = User::factory()->create(['role' => UserRole::PARTNER]);
    $this->partner1 = Partner::create([
        'user_id' => $this->partnerUser1->id,
        'business_name' => 'Tobatrans Rental Mobil',
        'email' => 'tobatrans@example.com',
        'city' => 'Medan Hub',
        'pic_phone' => '081234567890',
        'pic_name' => 'Budi Tobatrans',
        'status' => PartnerStatus::APPROVED,
        'average_rating' => 4.9,
    ]);

    $this->partnerUser2 = User::factory()->create(['role' => UserRole::PARTNER]);
    $this->partner2 = Partner::create([
        'user_id' => $this->partnerUser2->id,
        'business_name' => 'Horas Jaya Rental',
        'email' => 'horas@example.com',
        'city' => 'Berastagi',
        'pic_phone' => '089876543210',
        'pic_name' => 'Joko Horas',
        'status' => PartnerStatus::APPROVED,
        'average_rating' => 4.8,
    ]);

    // Create a Vehicle Product
    $this->vehicleProduct = Product::create([
        'partner_id' => $this->partner1->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::VEHICLE,
        'name' => ['id' => 'Toyota Innova Reborn All New', 'en' => 'Toyota Innova Reborn All New'],
        'slug' => 'toyota-innova-reborn-medan',
        'short_description' => ['id' => 'Rental Innova Reborn nyaman.', 'en' => 'Comfortable Innova Reborn.'],
        'full_description' => ['id' => 'Deskripsi lengkap rental mobil.', 'en' => 'Full rental car description.'],
        'base_price' => 750000,
        'pricing_type' => PricingType::PER_VEHICLE,
        'status' => ProductStatus::PUBLISHED,
    ]);

    // Create a Tour Product
    $this->tourProduct = Product::create([
        'partner_id' => $this->partner1->id,
        'ownership_type' => OwnershipType::INTERNAL,
        'product_type' => ProductType::TOUR,
        'name' => ['id' => 'Pesona Danau Toba & Samosir 3D2N', 'en' => 'Lake Toba Samosir Tour 3D2N'],
        'slug' => 'pesona-danau-toba-3d2n',
        'short_description' => ['id' => 'Tur danau toba lengkap.', 'en' => 'Complete lake toba tour.'],
        'full_description' => ['id' => 'Deskripsi tur.', 'en' => 'Tour description.'],
        'base_price' => 1500000,
        'pricing_type' => PricingType::PER_PERSON,
        'status' => ProductStatus::PUBLISHED,
    ]);

    // Create a Vehicle Rental Booking
    $this->rentalBooking = Booking::create([
        'booking_code' => 'MM-RENT-001',
        'user_id' => $this->customer->id,
        'product_id' => $this->vehicleProduct->id,
        'partner_id' => $this->partner1->id,
        'original_partner_id' => $this->partner1->id,
        'booking_status' => BookingStatus::PENDING_APPROVAL,
        'payment_status' => PaymentStatus::UNPAID,
        'inventory_status' => InventoryStatus::NONE,
        'service_start_date' => now()->addDays(2),
        'service_end_date' => now()->addDays(4),
        'service_start_time' => '08:00',
        'total_price' => 1500000,
        'dp_amount' => 450000,
        'total_paid' => 0,
        'commission_percentage' => 10.0,
        'platform_commission_amount' => 150000,
        'partner_payout_amount' => 1350000,
        'vendor_sla_deadline' => now()->addHours(24),
        'product_snapshot' => [
            'name' => 'Toyota Innova Reborn All New',
            'product_type' => 'VEHICLE',
        ],
        'pricing_breakdown' => [
            'base_price' => 750000,
            'duration_days' => 2,
            'subtotal' => 1500000,
        ],
        'cancellation_policy_snapshot' => [
            'type' => 'FLEXIBLE',
        ],
    ]);

    BookingVehicleDetail::create([
        'booking_id' => $this->rentalBooking->id,
        'pickup_location' => 'Bandara Internasional Kualanamu (KNO)',
        'dropoff_location' => 'Hotel Niagara Parapat',
        'driver_notes' => 'Tolong sediakan air mineral dan charger mobil.',
    ]);
});

test('unauthenticated user cannot access admin bookings management', function () {
    $response = $this->get(route('admin.bookings.index'));
    $response->assertRedirect(route('login'));
});

test('non-admin user gets forbidden on admin bookings management', function () {
    $response = $this->actingAs($this->customer)->get(route('admin.bookings.index'));
    $response->assertStatus(403);
});

test('admin can access central bookings management page and see vehicle rental bookings', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Bookings/Index')
        ->has('bookings.data', 1)
        ->where('bookings.data.0.booking_code', 'MM-RENT-001')
        ->where('bookings.data.0.product_type', 'VEHICLE')
        ->where('counts.vehicle_count', 1)
    );
});

test('admin can filter bookings by status and product type', function () {
    // Filter by VEHICLE
    $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', [
        'type' => 'VEHICLE',
    ]));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('bookings.data', 1)
        ->where('bookings.data.0.booking_code', 'MM-RENT-001')
    );

    // Filter by TOUR (should be 0)
    $responseTour = $this->actingAs($this->admin)->get(route('admin.bookings.index', [
        'type' => 'TOUR',
    ]));
    $responseTour->assertStatus(200);
    $responseTour->assertInertia(fn ($page) => $page
        ->has('bookings.data', 0)
    );
});

test('admin can search bookings by booking code or customer name', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', [
        'search' => 'MM-RENT',
    ]));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('bookings.data', 1)
        ->where('bookings.data.0.booking_code', 'MM-RENT-001')
    );
});

test('admin can view single booking detail page with vehicle details', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.bookings.show', $this->rentalBooking->id));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Bookings/Show')
        ->where('booking.booking_code', 'MM-RENT-001')
        ->where('booking.vehicle_detail.pickup_location', 'Bandara Internasional Kualanamu (KNO)')
        ->where('booking.partner.name', 'Tobatrans Rental Mobil')
        ->where('booking.partner_payout_amount', 1350000)
    );
});

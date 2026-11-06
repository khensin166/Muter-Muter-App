<?php

namespace Tests\Feature\Booking;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\TransmissionType;
use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->customer = User::create([
        'name' => 'Budi Santoso',
        'email' => 'budi@gmail.com',
        'phone' => '081234567890',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->partner = Partner::create([
        'business_name' => 'CV Horas Wisata Samosir',
        'pic_name' => 'Horas',
        'pic_phone' => '081199887766',
        'email' => 'horas@samosir.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
    ]);

    // Tour Product (Min Lead Time 24h / H-1)
    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => '3D2N Pesona Toba & Ferry',
        'slug' => '3d2n-pesona-toba-ferry',
        'short_description' => 'Tour komprehensif 3 hari',
        'full_description' => 'Termasuk tiket kapal ferry PP',
        'base_price' => 1500000,
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

    // Vehicle Product (Min Lead Time 12h)
    $this->vehicleProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::VEHICLE,
        'name' => 'Toyota Innova Reborn + Driver',
        'slug' => 'toyota-innova-reborn-driver',
        'short_description' => 'Mobil nyaman keluarga',
        'full_description' => 'Termasuk supir dan bbm',
        'base_price' => 850000,
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
        'model' => 'Innova Reborn',
        'manufacturing_year' => 2022,
        'passenger_capacity' => 7,
        'luggage_capacity' => 4,
        'transmission' => TransmissionType::AUTOMATIC,
        'with_driver' => true,
        'license_plate' => 'BK 5555 TO',
    ]);
});

test('guest is redirected to login when attempting to checkout without account', function () {
    $response = $this->post(route('checkout.store'), [
        'product_id' => $this->tourProduct->id,
        'service_start_date' => now()->addDays(3)->toDateString(),
        'participant_count' => 2,
    ]);

    $response->assertRedirect(route('login'));
});

test('customer can create tour booking with immutable snapshots and 24h SLA', function () {
    $startDate = now()->addDays(3)->toDateString();

    $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
        'product_id' => $this->tourProduct->id,
        'service_start_date' => $startDate,
        'participant_count' => 4,
        'special_requests' => 'Jemput di Bandara Silangit pukul 09.00',
    ]);

    $booking = Booking::where('user_id', $this->customer->id)->first();
    expect($booking)->not->toBeNull();

    $response->assertRedirect(route('bookings.show', $booking->booking_code));

    // Check Lifecycles
    expect($booking->booking_status)->toBe(BookingStatus::PENDING_APPROVAL);
    expect($booking->payment_status)->toBe(PaymentStatus::UNPAID);
    expect($booking->inventory_status)->toBe(InventoryStatus::NONE);

    // Check SLA (Must be ~24 hours from now)
    expect($booking->vendor_sla_deadline->isFuture())->toBeTrue();
    expect((int) round(now()->diffInHours($booking->vendor_sla_deadline)))->toBe(24);

    // Check Calculation: 1,500,000 * 4 = 6,000,000, DP 30% = 1,800,000
    expect((float) $booking->total_price)->toBe(6000000.0);
    expect((float) $booking->dp_amount)->toBe(1800000.0);

    // Check Snapshots
    expect($booking->product_snapshot['name'])->toBe('3D2N Pesona Toba & Ferry');
    expect($booking->product_snapshot['tour_details']['includes_ferry'])->toBeTrue();
    expect($booking->pricing_breakdown['quantity'])->toBe(4);
    expect($booking->cancellation_policy_snapshot['rules'])->toHaveCount(3);

    // Check Tour Detail record
    expect($booking->tourDetail)->not->toBeNull();
    expect($booking->tourDetail->participant_count)->toBe(4);
    expect($booking->tourDetail->special_requests)->toBe('Jemput di Bandara Silangit pukul 09.00');

    // Check Status History
    expect($booking->statusHistories)->toHaveCount(1);
    expect($booking->statusHistories->first()->new_status)->toBe(BookingStatus::PENDING_APPROVAL->value);
});

test('booking tour with less than 24h lead time fails with validation error', function () {
    // Attempt to book for 12 hours from now (< 24h)
    $tooSoonDate = now()->addHours(12)->toDateString();

    $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
        'product_id' => $this->tourProduct->id,
        'service_start_date' => $tooSoonDate,
        'service_start_time' => now()->addHours(12)->format('H:i'),
        'participant_count' => 2,
    ]);

    $response->assertSessionHasErrors('service_start_date');
    expect(Booking::count())->toBe(0);
});

test('customer can create vehicle booking with 12h lead time', function () {
    $startDate = now()->addDays(2)->toDateString();

    $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
        'product_id' => $this->vehicleProduct->id,
        'service_start_date' => $startDate,
        'service_start_time' => '08:00',
        'rental_days' => 3,
        'pickup_location' => 'Bandara Silangit',
        'dropoff_location' => 'Hotel Tuktuk Samosir',
        'driver_notes' => 'Driver merangkap pemandu',
    ]);

    $booking = Booking::where('user_id', $this->customer->id)->first();
    expect($booking)->not->toBeNull();

    // Check Calculation: 850,000 * 3 = 2,550,000, DP 30% = 765,000
    expect((float) $booking->total_price)->toBe(2550000.0);
    expect((float) $booking->dp_amount)->toBe(765000.0);

    expect($booking->vehicleDetail)->not->toBeNull();
    expect($booking->vehicleDetail->pickup_location)->toBe('Bandara Silangit');
    expect($booking->vehicleDetail->dropoff_location)->toBe('Hotel Tuktuk Samosir');
});

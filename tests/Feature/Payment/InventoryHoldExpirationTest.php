<?php

namespace Tests\Feature\Payment;

use App\Actions\Booking\CreateBookingAction;
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
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\InventoryHold;
use App\Models\Partner;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;

beforeEach(function () {
    $this->partner = Partner::create([
        'business_name' => 'CV Horas Wisata',
        'pic_name' => 'Horas',
        'pic_phone' => '081199887766',
        'email' => 'horas@samosir.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
    ]);

    $this->customer = User::create([
        'name' => 'Budi Santoso',
        'email' => 'budi@gmail.com',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Tour 3D2N Samosir',
        'slug' => 'tour-3d2n-samosir',
        'short_description' => 'Tour 3 hari',
        'full_description' => 'Full desc',
        'base_price' => 1000000,
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

    $action = new CreateBookingAction();
    $this->booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(3)->toDateString(),
        'participant_count' => 2,
    ]);

    // Active hold (Awaiting payment)
    $this->hold = InventoryHold::create([
        'booking_id' => $this->booking->id,
        'product_id' => $this->tourProduct->id,
        'held_quantity' => 2,
        'held_at' => now()->subHours(25),
        'expires_at' => now()->subHour(), // 1 hour ago (Expired!)
        'status' => InventoryStatus::HELD,
    ]);

    $this->booking->booking_status = BookingStatus::AWAITING_PAYMENT;
    $this->booking->inventory_status = InventoryStatus::HELD;
    $this->booking->save();
});

test('release expired holds command marks hold RELEASED and booking EXPIRED', function () {
    $this->artisan('inventory:release-expired-holds')
        ->expectsOutputToContain('Ditemukan 1 inventory hold kedaluwarsa')
        ->assertSuccessful();

    $this->hold->refresh();
    expect($this->hold->status)->toBe(InventoryStatus::RELEASED);

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::EXPIRED);
    expect($this->booking->payment_status)->toBe(PaymentStatus::OVERDUE);
    expect($this->booking->inventory_status)->toBe(InventoryStatus::RELEASED);

    // Verify history
    $lastHistory = $this->booking->statusHistories->last();
    expect($lastHistory->new_status)->toBe(BookingStatus::EXPIRED->value);
    expect($lastHistory->actor_type)->toBe('SYSTEM');
});


<?php

namespace Tests\Feature\Booking;

use App\Actions\Booking\CreateBookingAction;
use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerRole;
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
use App\Models\PartnerMember;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;

beforeEach(function () {
    // Partner A
    $this->partnerA = Partner::create([
        'business_name' => 'CV Horas Wisata Samosir',
        'pic_name' => 'Horas',
        'pic_phone' => '081199887766',
        'email' => 'horas@samosir.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
    ]);

    $this->ownerA = User::create([
        'name' => 'Horas Siregar',
        'email' => 'horas@samosir.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    PartnerMember::create([
        'partner_id' => $this->partnerA->id,
        'user_id' => $this->ownerA->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);

    // Partner B (Isolated tenant)
    $this->partnerB = Partner::create([
        'business_name' => 'CV Parapat Indah',
        'pic_name' => 'Tigor',
        'pic_phone' => '081233445566',
        'email' => 'tigor@parapat.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Parapat',
    ]);

    $this->ownerB = User::create([
        'name' => 'Tigor Manik',
        'email' => 'tigor@parapat.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    PartnerMember::create([
        'partner_id' => $this->partnerB->id,
        'user_id' => $this->ownerB->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);

    $this->customer = User::create([
        'name' => 'Budi',
        'email' => 'budi@gmail.com',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->tourProduct = Product::create([
        'partner_id' => $this->partnerA->id,
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
    $this->bookingA = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(3)->toDateString(),
        'participant_count' => 2,
    ]);
});

test('partner can view incoming booking requests inbox', function () {
    $response = $this->actingAs($this->ownerA)->get(route('partner.bookings.index'));

    $response->assertStatus(200);
});

test('partner B cannot view or manipulate booking belonging to Partner A', function () {
    $response = $this->actingAs($this->ownerB)->get(route('partner.bookings.show', $this->bookingA->id));

    $response->assertStatus(404);
});

test('partner 1-click approves availability: unlocks DP bill and creates 24h inventory hold', function () {
    $response = $this->actingAs($this->ownerA)->post(route('partner.bookings.approve', $this->bookingA->id));

    $response->assertRedirect(route('partner.bookings.index'));

    $this->bookingA->refresh();
    expect($this->bookingA->booking_status)->toBe(BookingStatus::AWAITING_PAYMENT);
    expect($this->bookingA->inventory_status)->toBe(InventoryStatus::HELD);

    // Verify 24-hour Temporary Inventory Hold record
    $hold = InventoryHold::where('booking_id', $this->bookingA->id)->first();
    expect($hold)->not->toBeNull();
    expect($hold->expires_at->isFuture())->toBeTrue();
    expect((int) round(now()->diffInHours($hold->expires_at)))->toBe(24);

    // Verify history log
    $lastHistory = $this->bookingA->statusHistories->last();
    expect($lastHistory->new_status)->toBe(BookingStatus::AWAITING_PAYMENT->value);
    expect($lastHistory->actor_type)->toBe('PARTNER');
});

test('partner can reject booking availability with reason', function () {
    $response = $this->actingAs($this->ownerA)->post(route('partner.bookings.reject', $this->bookingA->id), [
        'reason' => 'Semua armada dan guide sudah penuh pada tanggal tersebut.',
    ]);

    $response->assertRedirect(route('partner.bookings.index'));

    $this->bookingA->refresh();
    expect($this->bookingA->booking_status)->toBe(BookingStatus::REJECTED);
    expect($this->bookingA->inventory_status)->toBe(InventoryStatus::RELEASED);

    $lastHistory = $this->bookingA->statusHistories->last();
    expect($lastHistory->new_status)->toBe(BookingStatus::REJECTED->value);
    expect($lastHistory->reason)->toContain('Semua armada dan guide sudah penuh');
});

<?php

namespace Tests\Feature\Booking;

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
use App\Enums\RefundStatus;
use App\Enums\SettlementStatus;
use App\Enums\TourType;
use App\Enums\UserRole;
use App\Models\InventoryHold;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\VendorSettlement;

beforeEach(function () {
    $this->partner = Partner::create([
        'business_name' => 'Lake Toba Tour Partner',
        'pic_name' => 'Horas Situmorang',
        'pic_phone' => '08123456789',
        'email' => 'toba@partner.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '107-00-112233-4',
        'bank_account_holder' => 'Horas Situmorang',
    ]);

    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => '3D2N Explore Samosir & Ferry',
        'slug' => '3d2n-explore-samosir-ferry',
        'short_description' => 'Tour Danau Toba 3 Hari',
        'full_description' => 'Eksplorasi Tomok dan Tuktuk',
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

    $this->customer = User::create([
        'name' => 'Joko Anwar',
        'email' => 'joko@gmail.com',
        'phone' => '081199887766',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->admin = User::create([
        'name' => 'Super Admin Muter',
        'email' => 'admin@muter.com',
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);
});

test('customer cancels > 7 days before departure gets 100% refund of paid DP', function () {
    $action = new CreateBookingAction();
    // 10 days in advance (> 7 days)
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(10)->toDateString(),
        'participant_count' => 2,
    ]);

    // Simulate verified DP payment
    $booking->booking_status = BookingStatus::CONFIRMED;
    $booking->payment_status = PaymentStatus::PARTIALLY_PAID;
    $booking->inventory_status = InventoryStatus::ALLOCATED;
    $booking->total_paid = $booking->dp_amount; // 30% of 2,000,000 = 600,000
    $booking->save();

    InventoryHold::create([
        'booking_id' => $booking->id,
        'product_id' => $this->tourProduct->id,
        'status' => InventoryStatus::ALLOCATED,
        'held_at' => now(),
        'expires_at' => now()->addDays(1),
    ]);

    $response = $this->actingAs($this->customer)->post(route('bookings.cancel', $booking->booking_code), [
        'reason' => 'Perubahan mendadak jadwal kerja kantor',
        'bank_name' => 'BCA',
        'bank_account_number' => '5432109876',
        'bank_account_holder' => 'Joko Anwar',
    ]);

    $response->assertRedirect(route('bookings.show', $booking->booking_code));

    $booking->refresh();
    expect($booking->booking_status)->toBe(BookingStatus::REFUND_PENDING)
        ->and($booking->inventory_status)->toBe(InventoryStatus::RELEASED);

    // Verify Refund record
    $refund = Refund::where('booking_id', $booking->id)->first();
    expect($refund)->not->toBeNull()
        ->and((float) $refund->calculated_refund_percentage)->toBe(100.00)
        ->and((float) $refund->refund_amount)->toBe(600000.00)
        ->and($refund->status)->toBe(RefundStatus::PENDING)
        ->and($refund->bank_name)->toBe('BCA');

    // Verify inventory hold released
    $hold = InventoryHold::where('booking_id', $booking->id)->first();
    expect($hold->status)->toBe(InventoryStatus::RELEASED);
});

test('customer cancels 3 to 7 days before departure gets 50% refund of paid DP', function () {
    $action = new CreateBookingAction();
    // 5 days in advance (3 to 7 days)
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(5)->toDateString(),
        'participant_count' => 2,
    ]);

    $booking->booking_status = BookingStatus::CONFIRMED;
    $booking->payment_status = PaymentStatus::PARTIALLY_PAID;
    $booking->inventory_status = InventoryStatus::ALLOCATED;
    $booking->total_paid = $booking->dp_amount; // 600,000
    $booking->save();

    $response = $this->actingAs($this->customer)->post(route('bookings.cancel', $booking->booking_code), [
        'reason' => 'Ada acara keluarga mendadak',
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '1234567890',
        'bank_account_holder' => 'Joko Anwar',
    ]);

    $response->assertRedirect(route('bookings.show', $booking->booking_code));

    $booking->refresh();
    expect($booking->booking_status)->toBe(BookingStatus::REFUND_PENDING);

    $refund = Refund::where('booking_id', $booking->id)->first();
    expect($refund)->not->toBeNull()
        ->and((float) $refund->calculated_refund_percentage)->toBe(50.00)
        ->and((float) $refund->refund_amount)->toBe(300000.00); // 50% of 600,000
});

test('customer cancels less than 3 days before departure gets 0% refund and booking marked CANCELLED', function () {
    $action = new CreateBookingAction();
    // 2 days in advance (< 3 days)
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(2)->toDateString(),
        'participant_count' => 2,
    ]);

    $booking->booking_status = BookingStatus::CONFIRMED;
    $booking->payment_status = PaymentStatus::PARTIALLY_PAID;
    $booking->inventory_status = InventoryStatus::ALLOCATED;
    $booking->total_paid = $booking->dp_amount; // 600,000
    $booking->save();

    $response = $this->actingAs($this->customer)->post(route('bookings.cancel', $booking->booking_code), [
        'reason' => 'Batal mendadak H-2',
    ]);

    $response->assertRedirect(route('bookings.show', $booking->booking_code));

    $booking->refresh();
    // Non-refundable -> direct CANCELLED
    expect($booking->booking_status)->toBe(BookingStatus::CANCELLED)
        ->and($booking->inventory_status)->toBe(InventoryStatus::RELEASED);

    $refund = Refund::where('booking_id', $booking->id)->first();
    expect($refund)->toBeNull();
});

test('admin can process refund and transition booking to REFUNDED', function () {
    $action = new CreateBookingAction();
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(10)->toDateString(),
        'participant_count' => 2,
    ]);

    $booking->booking_status = BookingStatus::REFUND_PENDING;
    $booking->payment_status = PaymentStatus::REFUND_PENDING;
    $booking->total_paid = 600000;
    $booking->save();

    $refund = Refund::create([
        'booking_id' => $booking->id,
        'calculated_refund_percentage' => 100,
        'refund_amount' => 600000,
        'reason' => 'Perubahan jadwal',
        'bank_name' => 'BCA',
        'bank_account_number' => '1234567890',
        'bank_account_holder' => 'Joko Anwar',
        'status' => RefundStatus::PENDING,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.refunds.process', $refund->id), [
        'transfer_reference' => 'TRF-BCA-20261106-9901',
        'note' => 'Dana berhasil ditransfer via KlikBCA',
    ]);

    $response->assertRedirect(route('admin.refunds.index'));

    $refund->refresh();
    expect($refund->status)->toBe(RefundStatus::PROCESSED)
        ->and($refund->processed_by)->toBe($this->admin->id)
        ->and($refund->processed_at)->not->toBeNull();

    $booking->refresh();
    expect($booking->booking_status)->toBe(BookingStatus::REFUNDED)
        ->and($booking->payment_status)->toBe(PaymentStatus::REFUNDED);
});


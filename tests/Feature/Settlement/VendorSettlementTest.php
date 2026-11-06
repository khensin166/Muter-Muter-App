<?php

namespace Tests\Feature\Settlement;

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
use App\Enums\SettlementStatus;
use App\Enums\TourType;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\VendorSettlement;

beforeEach(function () {
    $this->partner = Partner::create([
        'business_name' => 'Horas Samosir Transport & Tour',
        'pic_name' => 'Baringin',
        'pic_phone' => '081299887766',
        'email' => 'horas@samosir.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '107-00-556677-8',
        'bank_account_holder' => 'Baringin',
    ]);

    $this->owner = User::create([
        'name' => 'Baringin Owner',
        'email' => 'owner@samosir.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    $this->staff = User::create([
        'name' => 'Staff Operasional',
        'email' => 'staff@samosir.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    PartnerMember::create([
        'partner_id' => $this->partner->id,
        'user_id' => $this->owner->id,
        'role' => 'OWNER',
        'is_active' => true,
    ]);

    PartnerMember::create([
        'partner_id' => $this->partner->id,
        'user_id' => $this->staff->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);

    $this->customer = User::create([
        'name' => 'Sari Indah',
        'email' => 'sari@gmail.com',
        'phone' => '081377889900',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->admin = User::create([
        'name' => 'Finance Admin',
        'email' => 'finance@muter.com',
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);

    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Samosir Day Trip Explorer',
        'slug' => 'samosir-day-trip-explorer',
        'short_description' => 'Tour 1 hari keliling samosir',
        'full_description' => 'Termasuk tiket masuk objek wisata',
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
        'duration_days' => 1,
        'duration_nights' => 0,
        'min_participants' => 1,
        'max_participants' => 10,
        'includes_ferry' => true,
    ]);
});

test('settlement is created with status PENDING when admin verifies payment', function () {
    $action = new CreateBookingAction();
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(5)->toDateString(),
        'participant_count' => 2,
    ]);

    // Create payment proof
    $payment = Payment::create([
        'booking_id' => $booking->id,
        'payment_code' => 'PAY-'.$booking->booking_code,
        'payment_type' => 'DOWN_PAYMENT',
        'amount' => $booking->dp_amount,
        'status' => 'PAYMENT_REVIEW',
    ]);

    PaymentProof::create([
        'payment_id' => $payment->id,
        'file_path' => 'payments/proofs/sample.jpg',
        'bank_sender_name' => 'BCA',
        'bank_sender_account' => '1234567890',
        'transferred_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.payments.approve', $payment->id));
    $response->assertRedirect(route('admin.payments.index'));

    $booking->refresh();
    expect($booking->booking_status)->toBe(BookingStatus::CONFIRMED);

    // Verify VendorSettlement created in PENDING status
    $settlement = VendorSettlement::where('booking_id', $booking->id)->first();
    expect($settlement)->not->toBeNull()
        ->and($settlement->status)->toBe(SettlementStatus::PENDING)
        ->and((float) $settlement->gross_amount)->toBe(2000000.00)
        ->and((float) $settlement->commission_amount)->toBe(200000.00) // 10%
        ->and((float) $settlement->net_payout_amount)->toBe(1800000.00); // 90%
});

test('mark completed trips command transitions past trips to COMPLETED and settlement to ELIGIBLE', function () {
    $action = new CreateBookingAction();
    // Create valid future booking first (bypassing lead-time check)
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(2)->toDateString(),
        'participant_count' => 2,
    ]);

    // Mutate date to past
    $booking->booking_status = BookingStatus::CONFIRMED;
    $booking->payment_status = PaymentStatus::PARTIALLY_PAID;
    $booking->service_start_date = now()->subDays(2);
    $booking->service_end_date = now()->subDays(1);
    $booking->save();

    $settlement = VendorSettlement::create([
        'partner_id' => $this->partner->id,
        'booking_id' => $booking->id,
        'gross_amount' => 2000000,
        'commission_amount' => 200000,
        'net_payout_amount' => 1800000,
        'status' => SettlementStatus::PENDING,
    ]);

    $this->artisan('trips:mark-completed')
        ->assertSuccessful();

    $booking->refresh();
    expect($booking->booking_status)->toBe(BookingStatus::COMPLETED);

    $settlement->refresh();
    expect($settlement->status)->toBe(SettlementStatus::ELIGIBLE)
        ->and($settlement->eligible_at)->not->toBeNull();
});

test('partner owner can view settlement ledger but staff is forbidden', function () {
    $responseOwner = $this->actingAs($this->owner)->get(route('partner.settlements.index'));
    $responseOwner->assertStatus(200);

    $responseStaff = $this->actingAs($this->staff)->get(route('partner.settlements.index'));
    $responseStaff->assertStatus(403);
});

test('admin can payout eligible settlement and record bank reference', function () {
    $action = new CreateBookingAction();
    $booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(3)->toDateString(),
        'participant_count' => 1,
    ]);

    $settlement = VendorSettlement::create([
        'partner_id' => $this->partner->id,
        'booking_id' => $booking->id,
        'gross_amount' => 1000000,
        'commission_amount' => 100000,
        'net_payout_amount' => 900000,
        'status' => SettlementStatus::ELIGIBLE,
        'eligible_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.settlements.payout', $settlement->id), [
        'bank_reference' => 'TRF-PAYOUT-202611-9988',
    ]);

    $response->assertRedirect(route('admin.settlements.index'));

    $settlement->refresh();
    expect($settlement->status)->toBe(SettlementStatus::PAID)
        ->and($settlement->bank_reference)->toBe('TRF-PAYOUT-202611-9988')
        ->and($settlement->paid_at)->not->toBeNull();
});


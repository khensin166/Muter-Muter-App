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
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\InventoryHold;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@mutermuter.com',
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);

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

    // Active hold
    $this->hold = InventoryHold::create([
        'booking_id' => $this->booking->id,
        'product_id' => $this->tourProduct->id,
        'held_quantity' => 2,
        'held_at' => now(),
        'expires_at' => now()->addHours(24),
        'status' => InventoryStatus::HELD,
    ]);

    $this->payment = Payment::create([
        'booking_id' => $this->booking->id,
        'payment_code' => 'PAY-20261106-0001',
        'payment_type' => 'DOWN_PAYMENT',
        'payment_method' => 'MANUAL_BANK_TRANSFER',
        'amount' => $this->booking->dp_amount,
        'status' => 'PAYMENT_REVIEW',
    ]);

    $this->proof = PaymentProof::create([
        'payment_id' => $this->payment->id,
        'file_path' => '/storage/payments/proofs/test.jpg',
        'bank_sender_name' => 'BCA',
        'bank_sender_account' => '1234567890',
        'bank_destination_name' => 'Bank Mandiri',
        'transferred_at' => now(),
    ]);

    $this->booking->booking_status = BookingStatus::PAYMENT_REVIEW;
    $this->booking->payment_status = PaymentStatus::PAYMENT_REVIEW;
    $this->booking->inventory_status = InventoryStatus::HELD;
    $this->booking->save();
});

test('admin can view payments verification queue', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

    $response->assertStatus(200);
});

test('admin can view single payment verification detail', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.payments.show', $this->payment->id));

    $response->assertStatus(200);
});

test('admin approves transfer proof: confirms booking, sets PARTIALLY_PAID, and allocates inventory', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.payments.approve', $this->payment->id));

    $response->assertRedirect(route('admin.payments.index'));

    $this->payment->refresh();
    expect($this->payment->status)->toBe('VERIFIED');
    expect($this->payment->paid_at)->not->toBeNull();

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::CONFIRMED);
    expect($this->booking->payment_status)->toBe(PaymentStatus::PARTIALLY_PAID);
    expect($this->booking->inventory_status)->toBe(InventoryStatus::ALLOCATED);
    expect((float) $this->booking->total_paid)->toBe((float) $this->payment->amount);

    // Verify InventoryHold is updated to ALLOCATED
    $this->hold->refresh();
    expect($this->hold->status)->toBe(InventoryStatus::ALLOCATED);

    // Verify AuditLog
    $audit = AuditLog::where('entity_id', $this->payment->id)->first();
    expect($audit)->not->toBeNull();
    expect($audit->action)->toBe('PAYMENT_VERIFIED');
});

test('admin can reject payment proof with explanation: returns booking to AWAITING_PAYMENT', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $this->payment->id), [
        'reason' => 'Mutasi bank belum masuk dan nominal transfer tidak cocok.',
    ]);

    $response->assertRedirect(route('admin.payments.index'));

    $this->payment->refresh();
    expect($this->payment->status)->toBe('REJECTED');

    $this->proof->refresh();
    expect($this->proof->verification_note)->toContain('Mutasi bank belum masuk');

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::AWAITING_PAYMENT);
    expect($this->booking->payment_status)->toBe(PaymentStatus::UNPAID);
});


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
use App\Models\Partner;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        'phone' => '081234567890',
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

    // Partner sets it to AWAITING_PAYMENT
    $this->booking->booking_status = BookingStatus::AWAITING_PAYMENT;
    $this->booking->inventory_status = InventoryStatus::HELD;
    $this->booking->save();
});

test('customer can upload transfer proof for down payment', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('struk_transfer.jpg');

    $response = $this->actingAs($this->customer)->post(route('bookings.payment-proof.store', $this->booking->booking_code), [
        'proof_file' => $file,
        'bank_sender_name' => 'Bank Central Asia (BCA)',
        'bank_sender_account' => '1234567890 a/n Budi Santoso',
    ]);

    $response->assertRedirect(route('bookings.show', $this->booking->booking_code));

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::PAYMENT_REVIEW);
    expect($this->booking->payment_status)->toBe(PaymentStatus::PAYMENT_REVIEW);

    // Verify Payment record
    $payment = Payment::where('booking_id', $this->booking->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount)->toBe((float) $this->booking->dp_amount);
    expect($payment->status)->toBe('PAYMENT_REVIEW');

    // Verify PaymentProof record
    $proof = PaymentProof::where('payment_id', $payment->id)->first();
    expect($proof)->not->toBeNull();
    expect($proof->bank_sender_name)->toBe('Bank Central Asia (BCA)');
    expect($proof->bank_sender_account)->toBe('1234567890 a/n Budi Santoso');

    // Verify history
    $lastHistory = $this->booking->statusHistories->last();
    expect($lastHistory->new_status)->toBe(BookingStatus::PAYMENT_REVIEW->value);
    expect($lastHistory->actor_type)->toBe('CUSTOMER');
});


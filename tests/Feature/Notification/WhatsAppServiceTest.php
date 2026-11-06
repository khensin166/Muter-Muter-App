<?php

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
use App\Models\Partner;
use App\Models\Product;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config()->set('whatsapp.mock_mode', true);
    config()->set('whatsapp.enabled', true);

    $this->partner = Partner::create([
        'business_name' => 'Toba Explorer Tour',
        'pic_name' => 'Horas',
        'pic_phone' => '081299887766',
        'email' => 'toba@explorer.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Parapat',
    ]);

    $this->tourProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Samosir Heritage Day Tour',
        'slug' => 'samosir-heritage-day-tour',
        'short_description' => 'Tour sejarah batak samosir',
        'full_description' => 'Eksplorasi Tomok dan Ambarita',
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
        'duration_days' => 1,
        'duration_nights' => 0,
        'min_participants' => 1,
        'max_participants' => 10,
        'includes_ferry' => true,
    ]);

    $this->customer = User::create([
        'name' => 'Budi Santoso',
        'email' => 'budi@test.com',
        'phone' => '081122334455',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $action = new CreateBookingAction();
    $this->booking = $action->execute($this->customer, $this->tourProduct, [
        'service_start_date' => now()->addDays(5)->toDateString(),
        'participant_count' => 2,
    ]);
});

test('whatsapp service formats indonesian phone numbers correctly', function () {
    $service = new WhatsAppService();

    expect($service->formatPhoneNumber('08123456789'))->toBe('628123456789')
        ->and($service->formatPhoneNumber('628123456789'))->toBe('628123456789')
        ->and($service->formatPhoneNumber('+62 812-3456-789'))->toBe('628123456789')
        ->and($service->formatPhoneNumber('8123456789'))->toBe('628123456789');
});

test('whatsapp service sends message in mock mode with log entry', function () {
    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, '[WhatsApp Mock Mode]') && str_contains($message, '628123456789');
        });

    $service = new WhatsAppService();
    $result = $service->sendMessage('08123456789', 'Halo ini pesan test Lake Toba!');

    expect($result)->toBeTrue();
});

test('notify vendor new booking sends notification with SLA details', function () {
    $service = new WhatsAppService();
    $dispatched = $service->notifyVendorNewBooking($this->booking);

    expect($dispatched)->toBeTrue();
});

test('notify customer booking approved sends notification with DP payment details', function () {
    $this->booking->booking_status = BookingStatus::AWAITING_PAYMENT;
    $this->booking->inventory_status = InventoryStatus::HELD;
    $this->booking->save();

    $service = new WhatsAppService();
    $dispatched = $service->notifyCustomerBookingApproved($this->booking);

    expect($dispatched)->toBeTrue();
});

test('notify customer payment confirmed sends confirmation with e-voucher link', function () {
    $this->booking->booking_status = BookingStatus::CONFIRMED;
    $this->booking->payment_status = PaymentStatus::PARTIALLY_PAID;
    $this->booking->inventory_status = InventoryStatus::ALLOCATED;
    $this->booking->total_paid = $this->booking->dp_amount;
    $this->booking->save();

    $service = new WhatsAppService();
    $dispatched = $service->notifyCustomerPaymentConfirmed($this->booking);

    expect($dispatched)->toBeTrue();
});

<?php

namespace Tests\Feature\Booking;

use App\Actions\Booking\CreateBookingAction;
use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\InventoryHold;
use App\Models\Partner;
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
        'name' => 'Budi',
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
        'short_description' => 'Tour',
        'full_description' => 'Tour',
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
});

test('sla timeout command escalates unresponded bookings after 24 hours to MANUAL_REVIEW', function () {
    // Manually backdate the SLA deadline to 1 hour ago
    $this->booking->vendor_sla_deadline = now()->subHour();
    $this->booking->save();

    $this->artisan('bookings:check-sla-timeouts')
        ->expectsOutputToContain('Ditemukan 1 pesanan yang melanggar batas SLA mitra 24 jam')
        ->assertSuccessful();

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::MANUAL_REVIEW);

    $lastHistory = $this->booking->statusHistories->last();
    expect($lastHistory->new_status)->toBe(BookingStatus::MANUAL_REVIEW->value);
    expect($lastHistory->actor_type)->toBe('SYSTEM');
});

test('admin can view manual review queue', function () {
    $this->booking->booking_status = BookingStatus::MANUAL_REVIEW;
    $this->booking->save();

    $response = $this->actingAs($this->admin)->get(route('admin.sla.index'));

    $response->assertStatus(200);
});

test('admin can force approve booking on behalf of unresponsive vendor', function () {
    $this->booking->booking_status = BookingStatus::MANUAL_REVIEW;
    $this->booking->save();

    $response = $this->actingAs($this->admin)->post(route('admin.sla.approve', $this->booking->id));

    $response->assertRedirect(route('admin.sla.index'));

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::AWAITING_PAYMENT);
    expect($this->booking->inventory_status)->toBe(InventoryStatus::HELD);

    // Verify 24h hold
    $hold = InventoryHold::where('booking_id', $this->booking->id)->first();
    expect($hold)->not->toBeNull();

    // Verify audit log
    $audit = AuditLog::where('entity_id', $this->booking->id)->first();
    expect($audit)->not->toBeNull();
    expect($audit->action)->toBe('BOOKING_FORCE_APPROVED');
});

test('admin can force cancel booking with reason', function () {
    $this->booking->booking_status = BookingStatus::MANUAL_REVIEW;
    $this->booking->save();

    $response = $this->actingAs($this->admin)->post(route('admin.sla.cancel', $this->booking->id), [
        'reason' => 'Vendor tidak dapat dihubungi dan menolak menerima order.',
    ]);

    $response->assertRedirect(route('admin.sla.index'));

    $this->booking->refresh();
    expect($this->booking->booking_status)->toBe(BookingStatus::CANCELLED);

    $audit = AuditLog::where('entity_id', $this->booking->id)->first();
    expect($audit)->not->toBeNull();
    expect($audit->action)->toBe('BOOKING_FORCE_CANCELLED');
});


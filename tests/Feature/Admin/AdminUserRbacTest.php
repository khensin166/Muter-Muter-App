<?php

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
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Super Administrator',
        'email' => 'admin@mutermuter.com',
        'role' => UserRole::ADMIN,
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Budi Traveler',
        'email' => 'budi@example.com',
        'role' => UserRole::CUSTOMER,
    ]);

    $this->partnerUser = User::factory()->create([
        'name' => 'Ucok Vendor',
        'email' => 'ucok@tobatrans.com',
        'role' => UserRole::PARTNER,
    ]);

    $this->partner = Partner::create([
        'business_name' => 'Tobatrans Rental Samosir',
        'email' => 'contact@tobatrans.com',
        'city' => 'Samosir',
        'pic_name' => 'Ucok Vendor',
        'pic_phone' => '081234567890',
        'status' => PartnerStatus::APPROVED,
        'average_rating' => 4.9,
    ]);

    PartnerMember::create([
        'partner_id' => $this->partner->id,
        'user_id' => $this->partnerUser->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);

    $this->vehicleProduct = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::VEHICLE,
        'name' => ['id' => 'Toyota Avanza All New', 'en' => 'Toyota Avanza All New'],
        'slug' => 'toyota-avanza-samosir',
        'short_description' => ['id' => 'Avanza nyaman.', 'en' => 'Comfortable Avanza.'],
        'full_description' => ['id' => 'Deskripsi.', 'en' => 'Description.'],
        'base_price' => 500000,
        'dp_percentage' => 30.0,
        'min_lead_time_hours' => 12,
        'pricing_type' => PricingType::PER_VEHICLE,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::APPROVED,
    ]);
});

test('unauthenticated user cannot access admin users rbac index', function () {
    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect(route('login'));
});

test('non-admin user gets forbidden on admin users rbac index', function () {
    $response = $this->actingAs($this->customer)->get(route('admin.users.index'));
    $response->assertStatus(403);
});

test('admin can access users rbac management index', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Index')
        ->has('users.data')
        ->where('counts.customer', 1)
        ->where('counts.partner', 1)
        ->where('counts.admin', 1)
    );
});

test('admin can filter users by role and search keyword', function () {
    // Search by name "Budi"
    $responseSearch = $this->actingAs($this->admin)->get(route('admin.users.index', [
        'search' => 'Budi',
    ]));
    $responseSearch->assertStatus(200);
    $responseSearch->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.email', 'budi@example.com')
    );

    // Filter by role PARTNER
    $responseRole = $this->actingAs($this->admin)->get(route('admin.users.index', [
        'role' => 'PARTNER',
    ]));
    $responseRole->assertStatus(200);
    $responseRole->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.email', 'ucok@tobatrans.com')
    );
});

test('admin can change user role from CUSTOMER to ADMIN', function () {
    $response = $this->actingAs($this->admin)->patch(route('admin.users.update-role', $this->customer->id), [
        'role' => 'ADMIN',
    ]);

    $response->assertSessionHas('success');
    expect($this->customer->fresh()->role)->toBe(UserRole::ADMIN);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'USER_ROLE_UPDATED',
        'user_id' => $this->admin->id,
        'entity_type' => User::class,
        'entity_id' => $this->customer->id,
    ]);
});

test('admin can change user role to PARTNER and link to partner company', function () {
    $newCustomer = User::factory()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.users.update-role', $newCustomer->id), [
        'role' => 'PARTNER',
        'partner_id' => $this->partner->id,
        'partner_role' => 'STAFF',
    ]);

    $response->assertSessionHas('success');
    expect($newCustomer->fresh()->role)->toBe(UserRole::PARTNER);

    $this->assertDatabaseHas('partner_members', [
        'user_id' => $newCustomer->id,
        'partner_id' => $this->partner->id,
        'role' => 'STAFF',
    ]);
});

test('admin cannot change own role to prevent lockout', function () {
    $response = $this->actingAs($this->admin)->patch(route('admin.users.update-role', $this->admin->id), [
        'role' => 'CUSTOMER',
    ]);

    $response->assertSessionHas('error');
    expect($this->admin->fresh()->role)->toBe(UserRole::ADMIN);
});

test('admin user cannot make a booking', function () {
    $response = $this->actingAs($this->admin)->post(route('checkout.store'), [
        'product_id' => $this->vehicleProduct->id,
        'service_start_date' => now()->addDays(2)->format('Y-m-d'),
        'rental_days' => 2,
    ]);

    $response->assertSessionHasErrors('booking');
});

test('partner user cannot make a booking', function () {
    $response = $this->actingAs($this->partnerUser)->post(route('checkout.store'), [
        'product_id' => $this->vehicleProduct->id,
        'service_start_date' => now()->addDays(2)->format('Y-m-d'),
        'rental_days' => 2,
    ]);

    $response->assertSessionHasErrors('booking');
});

test('customer user can make a booking successfully', function () {
    $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
        'product_id' => $this->vehicleProduct->id,
        'service_start_date' => now()->addDays(2)->format('Y-m-d'),
        'rental_days' => 2,
        'pickup_location' => 'Bandara Silangit',
        'dropoff_location' => 'Hotel Niagara',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('bookings', [
        'user_id' => $this->customer->id,
        'product_id' => $this->vehicleProduct->id,
        'booking_status' => BookingStatus::PENDING_APPROVAL,
    ]);
});


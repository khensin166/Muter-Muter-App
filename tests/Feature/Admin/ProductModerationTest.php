<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductApprovalLog;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@mutermuter.com',
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);

    $this->partner = Partner::create([
        'business_name' => 'CV Lake Toba Explorer',
        'pic_name' => 'Bona',
        'pic_phone' => '08123456789',
        'email' => 'bona@explorer.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Parapat',
    ]);

    $this->product = Product::create([
        'partner_id' => $this->partner->id,
        'ownership_type' => OwnershipType::PARTNER,
        'product_type' => ProductType::TOUR,
        'name' => 'Paket Tour Menunggu Moderasi',
        'slug' => 'paket-tour-menunggu-moderasi',
        'short_description' => 'Short desc',
        'full_description' => 'Full desc',
        'base_price' => 500000,
        'pricing_type' => PricingType::PER_PERSON,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 24,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::PENDING_REVIEW,
    ]);
});

test('admin can view products moderation queue', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

    $response->assertStatus(200);
});

test('admin can view single product moderation detail', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.products.show', $this->product->id));

    $response->assertStatus(200);
});

test('admin can approve pending product and publish to marketplace', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.products.approve', $this->product->id));

    $response->assertRedirect(route('admin.products.index'));

    $this->product->refresh();
    expect($this->product->approval_status)->toBe(ApprovalStatus::APPROVED);
    expect($this->product->status)->toBe(ProductStatus::PUBLISHED);

    // Verify approval log
    $log = ProductApprovalLog::where('product_id', $this->product->id)->first();
    expect($log)->not->toBeNull();
    expect($log->new_status)->toBe(ApprovalStatus::APPROVED->value);

    // Verify audit log
    $audit = AuditLog::where('entity_id', $this->product->id)->first();
    expect($audit)->not->toBeNull();
    expect($audit->action)->toBe('PRODUCT_APPROVED');
});

test('admin can reject pending product with reason', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.products.reject', $this->product->id), [
        'reason' => 'Deskripsi tidak sesuai standar operasional dan rute tidak jelas.',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $this->product->refresh();
    expect($this->product->approval_status)->toBe(ApprovalStatus::REJECTED);

    // Verify approval log
    $log = ProductApprovalLog::where('product_id', $this->product->id)->first();
    expect($log)->not->toBeNull();
    expect($log->new_status)->toBe(ApprovalStatus::REJECTED->value);
    expect($log->reason)->toContain('Deskripsi tidak sesuai standar');
});

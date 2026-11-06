<?php

namespace Tests\Feature\Admin;

use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);

    $this->customer = User::create([
        'name' => 'Customer Test',
        'email' => 'customer@test.com',
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $this->pendingPartner = Partner::create([
        'business_name' => 'CV Pending Test',
        'pic_name' => 'Pending Owner',
        'pic_phone' => '0812345678',
        'email' => 'pending@test.com',
        'status' => PartnerStatus::PENDING,
        'city' => 'Samosir',
    ]);
});

test('non-admin cannot access partner verification panel', function () {
    $response = $this->actingAs($this->customer)->get(route('admin.partners.index'));

    $response->assertStatus(403);
});

test('admin can view partner verification list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.partners.index'));

    $response->assertStatus(200);
});

test('admin can approve a pending partner', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.partners.approve', $this->pendingPartner->id));

    $response->assertRedirect(route('admin.partners.index'));

    $this->pendingPartner->refresh();
    expect($this->pendingPartner->status)->toBe(PartnerStatus::APPROVED);
});

test('admin can reject a pending partner with a reason', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.partners.reject', $this->pendingPartner->id), [
        'reason' => 'Dokumen NIB tidak valid atau masa berlaku habis.',
    ]);

    $response->assertRedirect(route('admin.partners.index'));

    $this->pendingPartner->refresh();
    expect($this->pendingPartner->status)->toBe(PartnerStatus::REJECTED);
});


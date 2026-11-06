<?php

namespace Tests\Feature\Partner;

use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\User;

beforeEach(function () {
    $this->partnerA = Partner::create([
        'business_name' => 'Partner A Tour',
        'pic_name' => 'Owner A',
        'pic_phone' => '0811111111',
        'email' => 'partnerA@test.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Samosir',
    ]);

    $this->ownerA = User::create([
        'name' => 'Owner A',
        'email' => 'ownerA@test.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    PartnerMember::create([
        'partner_id' => $this->partnerA->id,
        'user_id' => $this->ownerA->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);

    $this->staffA = User::create([
        'name' => 'Staff A',
        'email' => 'staffA@test.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    $this->memberStaffA = PartnerMember::create([
        'partner_id' => $this->partnerA->id,
        'user_id' => $this->staffA->id,
        'role' => PartnerRole::STAFF,
        'is_active' => true,
    ]);

    // Partner B (Isolated tenant)
    $this->partnerB = Partner::create([
        'business_name' => 'Partner B Tour',
        'pic_name' => 'Owner B',
        'pic_phone' => '0822222222',
        'email' => 'partnerB@test.com',
        'status' => PartnerStatus::APPROVED,
        'city' => 'Parapat',
    ]);

    $this->ownerB = User::create([
        'name' => 'Owner B',
        'email' => 'ownerB@test.com',
        'role' => UserRole::PARTNER,
        'password' => bcrypt('password'),
    ]);

    $this->memberStaffB = PartnerMember::create([
        'partner_id' => $this->partnerB->id,
        'user_id' => $this->ownerB->id,
        'role' => PartnerRole::OWNER,
        'is_active' => true,
    ]);
});

test('partner owner can view staff list', function () {
    $response = $this->actingAs($this->ownerA)->get(route('partner.staff.index'));

    $response->assertStatus(200);
});

test('partner owner can invite new staff member', function () {
    $response = $this->actingAs($this->ownerA)->post(route('partner.staff.store'), [
        'name' => 'Staff Baru',
        'email' => 'staffbaru@test.com',
        'phone' => '0812334455',
        'password' => 'password123',
    ]);

    $response->assertSessionHas('success');

    $newStaff = User::where('email', 'staffbaru@test.com')->first();
    expect($newStaff)->not->toBeNull();

    $membership = PartnerMember::where('partner_id', $this->partnerA->id)
        ->where('user_id', $newStaff->id)
        ->first();

    expect($membership)->not->toBeNull();
    expect($membership->role)->toBe(PartnerRole::STAFF);
});

test('partner staff cannot invite other staff members', function () {
    $response = $this->actingAs($this->staffA)->post(route('partner.staff.store'), [
        'name' => 'Unauthorized Staff',
        'email' => 'unauthorized@test.com',
        'phone' => '0812999999',
        'password' => 'password123',
    ]);

    $response->assertStatus(403);
});

test('partner A cannot delete staff belonging to Partner B', function () {
    $response = $this->actingAs($this->ownerA)->delete(route('partner.staff.destroy', $this->memberStaffB->id));

    $response->assertStatus(404);
});


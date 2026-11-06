<?php

namespace Tests\Feature\Partner;

use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('partner registration screen can be rendered', function () {
    $response = $this->get(route('partner.register'));

    $response->assertStatus(200);
});

test('partner can register with valid information and kyc documents', function () {
    Storage::fake('public');

    $response = $this->post(route('partner.register.store'), [
        'business_name' => 'CV Danau Toba Sejahtera',
        'brand_name' => 'Toba Sejahtera Tour',
        'city' => 'Samosir',
        'address' => 'Jl. Lingkar Samosir',
        'pic_name' => 'Horas Siregar',
        'pic_phone' => '081299990001',
        'email' => 'horas@tobasejahtera.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '1070099887766',
        'bank_account_holder' => 'CV Danau Toba Sejahtera',
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'nib_file' => UploadedFile::fake()->create('nib.pdf', 500, 'application/pdf'),
        'fleet_photos' => [
            UploadedFile::fake()->image('unit1.jpg'),
            UploadedFile::fake()->image('unit2.jpg'),
        ],
    ]);

    $response->assertRedirect(route('partner.dashboard'));

    // Check Partner Record
    $partner = Partner::where('email', 'horas@tobasejahtera.com')->first();
    expect($partner)->not->toBeNull();
    expect($partner->status)->toBe(PartnerStatus::PENDING);

    // Check User Record
    $user = User::where('email', 'horas@tobasejahtera.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe(UserRole::PARTNER);
    expect($user->isPartnerOwner($partner))->toBeTrue();

    // Check Documents (1 KTP, 1 NIB, 2 fleet photos)
    expect($partner->documents)->toHaveCount(4);
});

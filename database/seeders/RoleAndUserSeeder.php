<?php

namespace Database\Seeders;

use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@mutermuter.com'],
            [
                'name' => 'Super Administrator Platform',
                'phone' => '081260000001',
                'role' => UserRole::SUPER_ADMIN,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Partner (Horas Samosir Tour & Transport)
        $partner = Partner::firstOrCreate(
            ['email' => 'contact@horassamosir.com'],
            [
                'business_name' => 'CV Horas Samosir Wisata',
                'brand_name' => 'Horas Samosir Tour & Transport',
                'pic_name' => 'Togu Simanjuntak',
                'pic_phone' => '081260000002',
                'address' => 'Jl. Lintas Tomok No. 18',
                'city' => 'Samosir',
                'status' => PartnerStatus::APPROVED,
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '1070019283741',
                'bank_account_holder' => 'CV Horas Samosir Wisata',
            ]
        );

        // 2a. Partner Owner User
        $partnerOwner = User::firstOrCreate(
            ['email' => 'togu@horassamosir.com'],
            [
                'name' => 'Togu Simanjuntak',
                'phone' => '081260000002',
                'role' => UserRole::PARTNER,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        PartnerMember::firstOrCreate(
            [
                'partner_id' => $partner->id,
                'user_id' => $partnerOwner->id,
            ],
            [
                'role' => PartnerRole::OWNER,
                'is_active' => true,
            ]
        );

        // 2b. Partner Staff User
        $partnerStaff = User::firstOrCreate(
            ['email' => 'butet@horassamosir.com'],
            [
                'name' => 'Butet Nainggolan',
                'phone' => '081260000003',
                'role' => UserRole::PARTNER,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        PartnerMember::firstOrCreate(
            [
                'partner_id' => $partner->id,
                'user_id' => $partnerStaff->id,
            ],
            [
                'role' => PartnerRole::STAFF,
                'is_active' => true,
            ]
        );

        // 3. Demo Customer User
        $customer = User::firstOrCreate(
            ['email' => 'budi.santoso@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081370000001',
                'role' => UserRole::CUSTOMER,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}


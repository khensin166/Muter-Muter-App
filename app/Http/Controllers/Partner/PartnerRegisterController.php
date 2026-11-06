<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerDocument;
use App\Models\PartnerMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRegisterController extends Controller
{
    /**
     * Show the partner registration form.
     */
    public function create(): Response
    {
        return Inertia::render('Partner/Register');
    }

    /**
     * Handle incoming partner registration and KYC submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Usaha / Profil Vendor
            'business_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],

            // PIC & Akun
            'pic_name' => ['required', 'string', 'max:255'],
            'pic_phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:partners,email'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Payout Bank
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_holder' => ['required', 'string', 'max:255'],

            // Dokumen KYC
            'ktp_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'nib_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'fleet_photos' => ['required', 'array', 'min:1'],
            'fleet_photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            // 1. Create Partner Entity
            $partner = Partner::create([
                'business_name' => $validated['business_name'],
                'brand_name' => $validated['brand_name'] ?? $validated['business_name'],
                'pic_name' => $validated['pic_name'],
                'pic_phone' => $validated['pic_phone'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
                'status' => PartnerStatus::PENDING,
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_holder' => $validated['bank_account_holder'],
            ]);

            // 2. Create User Account
            $user = User::create([
                'name' => $validated['pic_name'],
                'email' => $validated['email'],
                'phone' => $validated['pic_phone'],
                'role' => UserRole::PARTNER,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            // 3. Link as Partner Owner
            PartnerMember::create([
                'partner_id' => $partner->id,
                'user_id' => $user->id,
                'role' => PartnerRole::OWNER,
                'is_active' => true,
            ]);

            // 4. Save KYC Documents
            if ($request->hasFile('ktp_file')) {
                $ktpPath = $request->file('ktp_file')->store('partner_documents/ktp', 'public');
                PartnerDocument::create([
                    'partner_id' => $partner->id,
                    'document_type' => 'KTP',
                    'file_path' => $ktpPath,
                    'verification_status' => 'PENDING',
                ]);
            }

            if ($request->hasFile('nib_file')) {
                $nibPath = $request->file('nib_file')->store('partner_documents/nib', 'public');
                PartnerDocument::create([
                    'partner_id' => $partner->id,
                    'document_type' => 'NIB',
                    'file_path' => $nibPath,
                    'verification_status' => 'PENDING',
                ]);
            }

            if ($request->hasFile('fleet_photos')) {
                foreach ($request->file('fleet_photos') as $photo) {
                    $photoPath = $photo->store('partner_documents/fleet', 'public');
                    PartnerDocument::create([
                        'partner_id' => $partner->id,
                        'document_type' => 'FLEET_PHOTO',
                        'file_path' => $photoPath,
                        'verification_status' => 'PENDING',
                    ]);
                }
            }

            Auth::login($user);
        });

        return redirect()->route('partner.dashboard')->with('success', 'Pendaftaran mitra berhasil! Dokumen Anda sedang ditinjau oleh Administrator.');
    }
}


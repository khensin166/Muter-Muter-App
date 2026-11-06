<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PartnerMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PartnerStaffController extends Controller
{
    /**
     * Display a listing of partner staff.
     */
    public function index(Request $request): Response
    {
        $partner = $request->user()->currentPartner();
        $this->authorize('manageStaff', $partner);

        $staffMembers = PartnerMember::where('partner_id', $partner->id)
            ->with('user')
            ->orderBy('role', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'name' => $m->user?->name,
                    'email' => $m->user?->email,
                    'phone' => $m->user?->phone,
                    'role' => $m->role->value,
                    'is_active' => $m->is_active,
                    'joined_at' => $m->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Partner/Staff/Index', [
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->business_name,
            ],
            'staffMembers' => $staffMembers,
        ]);
    }

    /**
     * Store a newly created partner staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $partner = $request->user()->currentPartner();
        $this->authorize('manageStaff', $partner);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', Password::defaults()],
        ]);

        DB::transaction(function () use ($partner, $validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => UserRole::PARTNER,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            PartnerMember::create([
                'partner_id' => $partner->id,
                'user_id' => $user->id,
                'role' => PartnerRole::STAFF,
                'is_active' => true,
            ]);
        });

        return back()->with('success', 'Staf operasional baru berhasil ditambahkan.');
    }

    /**
     * Toggle active status of a staff member.
     */
    public function toggleStatus(Request $request, string $id): RedirectResponse
    {
        $partner = $request->user()->currentPartner();
        $this->authorize('manageStaff', $partner);

        $member = PartnerMember::where('partner_id', $partner->id)->where('id', $id)->firstOrFail();

        if ($member->role === PartnerRole::OWNER) {
            return back()->with('error', 'Status akun Owner utama tidak dapat dinonaktifkan.');
        }

        $member->update(['is_active' => ! $member->is_active]);

        return back()->with('success', 'Status staf berhasil diperbarui.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $partner = $request->user()->currentPartner();
        $this->authorize('manageStaff', $partner);

        $member = PartnerMember::where('partner_id', $partner->id)->where('id', $id)->firstOrFail();

        if ($member->role === PartnerRole::OWNER) {
            return back()->with('error', 'Akun Owner utama tidak dapat dihapus.');
        }

        $member->delete();

        return back()->with('success', 'Staf berhasil dihapus dari mitra.');
    }
}


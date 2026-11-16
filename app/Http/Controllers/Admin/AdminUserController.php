<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerRole;
use App\Enums\PartnerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Partner;
use App\Models\PartnerMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Display a comprehensive listing of all registered users and their RBAC roles.
     */
    public function index(Request $request): Response
    {
        $roleFilter = $request->query('role', 'ALL');
        $search = $request->query('search', '');

        $query = User::query()
            ->with(['partnerMemberships.partner'])
            ->withCount('bookings');

        if ($roleFilter !== 'ALL' && UserRole::tryFrom($roleFilter)) {
            $query->where('role', $roleFilter);
        }

        if (!empty($search)) {
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhere('email', $like, "%{$search}%")
                    ->orWhere('phone', $like, "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString()->through(function ($u) {
            $primaryPartner = $u->partnerMemberships->firstWhere('is_active', true)?->partner;
            $partnerRole = $u->partnerMemberships->firstWhere('is_active', true)?->role?->value;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role->value,
                'is_admin' => $u->isAdmin(),
                'is_partner' => $u->isPartner(),
                'is_customer' => $u->isCustomer(),
                'partner' => $primaryPartner ? [
                    'id' => $primaryPartner->id,
                    'name' => $primaryPartner->business_name,
                    'city' => $primaryPartner->city,
                    'role' => $partnerRole,
                ] : null,
                'bookings_count' => $u->bookings_count,
                'created_at' => $u->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'all' => User::count(),
            'customer' => User::where('role', UserRole::CUSTOMER)->count(),
            'partner' => User::where('role', UserRole::PARTNER)->count(),
            'admin' => User::whereIn('role', [UserRole::ADMIN, UserRole::SUPER_ADMIN])->count(),
        ];

        $approvedPartners = Partner::where('status', PartnerStatus::APPROVED)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'city']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'counts' => $counts,
            'partners' => $approvedPartners,
            'filters' => [
                'role' => $roleFilter,
                'search' => $search,
            ],
            'currentAdminId' => $request->user()->id,
        ]);
    }

    /**
     * Update a user's RBAC role and optional partner association.
     */
    public function updateRole(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::enum(UserRole::class)],
            'partner_id' => ['nullable', 'uuid', 'exists:partners,id'],
            'partner_role' => ['nullable', 'string', Rule::enum(PartnerRole::class)],
        ]);

        $newRole = UserRole::from($validated['role']);
        $oldRole = $user->role->value;

        // Security Guard: Prevent admin from demoting or locking out themselves
        if ($request->user()->id === $user->id && $newRole !== $user->role) {
            return back()->with('error', 'Demi keamanan sistem, Anda tidak dapat mengubah peran atau menurunkan hak akses akun Anda sendiri.');
        }

        DB::transaction(function () use ($user, $newRole, $oldRole, $validated, $request) {
            // Update User Role
            $user->update([
                'role' => $newRole,
            ]);

            // Handle Partner Linkage if role is PARTNER
            if ($newRole === UserRole::PARTNER && !empty($validated['partner_id'])) {
                PartnerMember::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'partner_id' => $validated['partner_id'],
                    ],
                    [
                        'role' => !empty($validated['partner_role']) ? PartnerRole::from($validated['partner_role']) : PartnerRole::OWNER,
                        'is_active' => true,
                    ]
                );
            }

            // Create Audit Trail Log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'USER_ROLE_UPDATED',
                'entity_type' => User::class,
                'entity_id' => $user->id,
                'old_values' => ['role' => $oldRole],
                'new_values' => [
                    'role' => $newRole->value,
                    'partner_id' => $validated['partner_id'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', "Hak akses untuk {$user->name} berhasil diperbarui menjadi {$newRole->value}.");
    }
}


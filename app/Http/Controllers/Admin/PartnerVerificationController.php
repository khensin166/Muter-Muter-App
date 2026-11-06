<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerVerificationController extends Controller
{
    /**
     * Display a listing of partners for verification.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'PENDING');

        $query = Partner::query()->withCount(['products', 'members']);

        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $partners = $query->orderBy('created_at', 'desc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'business_name' => $p->business_name,
                'brand_name' => $p->brand_name,
                'pic_name' => $p->pic_name,
                'pic_phone' => $p->pic_phone,
                'email' => $p->email,
                'city' => $p->city,
                'status' => $p->status->value,
                'products_count' => $p->products_count,
                'members_count' => $p->members_count,
                'registered_at' => $p->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'pending' => Partner::where('status', PartnerStatus::PENDING)->count(),
            'approved' => Partner::where('status', PartnerStatus::APPROVED)->count(),
            'suspended' => Partner::where('status', PartnerStatus::SUSPENDED)->count(),
            'rejected' => Partner::where('status', PartnerStatus::REJECTED)->count(),
        ];

        return Inertia::render('Admin/Partners/Index', [
            'partners' => $partners,
            'currentStatus' => $statusFilter,
            'counts' => $counts,
        ]);
    }

    /**
     * Display the specified partner and documents.
     */
    public function show(string $id): Response
    {
        $partner = Partner::with(['documents', 'members.user', 'products'])->findOrFail($id);

        return Inertia::render('Admin/Partners/Show', [
            'partner' => [
                'id' => $partner->id,
                'business_name' => $partner->business_name,
                'brand_name' => $partner->brand_name,
                'pic_name' => $partner->pic_name,
                'pic_phone' => $partner->pic_phone,
                'email' => $partner->email,
                'address' => $partner->address,
                'city' => $partner->city,
                'status' => $partner->status->value,
                'bank_name' => $partner->bank_name,
                'bank_account_number' => $partner->bank_account_number,
                'bank_account_holder' => $partner->bank_account_holder,
                'registered_at' => $partner->created_at->format('d M Y, H:i'),
            ],
            'documents' => $partner->documents->map(function ($d) {
                return [
                    'id' => $d->id,
                    'type' => $d->document_type,
                    'file_path' => asset('storage/' . $d->file_path),
                    'status' => $d->verification_status,
                    'rejection_reason' => $d->rejection_reason,
                ];
            }),
            'members' => $partner->members->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->user?->name,
                    'email' => $m->user?->email,
                    'role' => $m->role->value,
                    'is_active' => $m->is_active,
                ];
            }),
        ]);
    }

    /**
     * Approve the partner.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $partner = Partner::findOrFail($id);
        $oldStatus = $partner->status->value;

        $partner->update(['status' => PartnerStatus::APPROVED]);
        $partner->documents()->update(['verification_status' => 'APPROVED']);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'PARTNER_APPROVED',
            'entity_type' => Partner::class,
            'entity_id' => $partner->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => PartnerStatus::APPROVED->value],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.partners.index')->with('success', "Mitra {$partner->business_name} berhasil diverifikasi dan disetujui.");
    }

    /**
     * Reject the partner with reason.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $partner = Partner::findOrFail($id);
        $oldStatus = $partner->status->value;

        $partner->update(['status' => PartnerStatus::REJECTED]);
        $partner->documents()->update([
            'verification_status' => 'REJECTED',
            'rejection_reason' => $request->reason,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'PARTNER_REJECTED',
            'entity_type' => Partner::class,
            'entity_id' => $partner->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => PartnerStatus::REJECTED->value,
                'reason' => $request->reason,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.partners.index')->with('success', "Pendaftaran mitra {$partner->business_name} ditolak.");
    }

    /**
     * Suspend the partner.
     */
    public function suspend(Request $request, string $id): RedirectResponse
    {
        $partner = Partner::findOrFail($id);
        $oldStatus = $partner->status->value;

        $partner->update(['status' => PartnerStatus::SUSPENDED]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'PARTNER_SUSPENDED',
            'entity_type' => Partner::class,
            'entity_id' => $partner->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => PartnerStatus::SUSPENDED->value],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Mitra {$partner->business_name} berhasil disuspend.");
    }
}


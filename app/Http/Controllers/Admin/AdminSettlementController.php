<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VendorSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettlementController extends Controller
{
    /**
     * Display listing of vendor settlements for admin payout execution.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'ELIGIBLE');

        $query = VendorSettlement::query()->with(['partner', 'booking.user']);

        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $settlements = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($s) {
            return [
                'id' => $s->id,
                'booking_code' => $s->booking?->booking_code,
                'partner_id' => $s->partner_id,
                'partner_name' => $s->partner?->business_name,
                'partner_bank' => [
                    'bank_name' => $s->partner?->bank_name,
                    'bank_account_number' => $s->partner?->bank_account_number,
                    'bank_account_holder' => $s->partner?->bank_account_holder,
                ],
                'product_name' => $s->booking?->product_snapshot['name'] ?? 'Paket Wisata',
                'customer_name' => $s->booking?->user?->name,
                'service_date' => $s->booking?->service_start_date->format('d M Y'),
                'gross_amount' => (float) $s->gross_amount,
                'commission_amount' => (float) $s->commission_amount,
                'net_payout_amount' => (float) $s->net_payout_amount,
                'status' => $s->status->value,
                'bank_reference' => $s->bank_reference,
                'eligible_at' => $s->eligible_at?->format('d M Y, H:i'),
                'paid_at' => $s->paid_at?->format('d M Y, H:i'),
                'created_at' => $s->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'eligible' => VendorSettlement::where('status', SettlementStatus::ELIGIBLE)->count(),
            'pending' => VendorSettlement::where('status', SettlementStatus::PENDING)->count(),
            'paid' => VendorSettlement::where('status', SettlementStatus::PAID)->count(),
            'on_hold' => VendorSettlement::where('status', SettlementStatus::ON_HOLD)->count(),
            'all' => VendorSettlement::count(),
        ];

        $totals = [
            'eligible_payout' => (float) VendorSettlement::where('status', SettlementStatus::ELIGIBLE)->sum('net_payout_amount'),
            'total_commission_collected' => (float) VendorSettlement::whereIn('status', [SettlementStatus::ELIGIBLE, SettlementStatus::PAID])->sum('commission_amount'),
            'total_paid_out' => (float) VendorSettlement::where('status', SettlementStatus::PAID)->sum('net_payout_amount'),
        ];

        return Inertia::render('Admin/Settlements/Index', [
            'settlements' => $settlements,
            'counts' => $counts,
            'totals' => $totals,
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Execute payout transfer to vendor with bank reference number.
     */
    public function payout(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'bank_reference' => ['required', 'string', 'min:3', 'max:100'],
        ]);

        $admin = $request->user();
        $settlement = VendorSettlement::with('partner')->findOrFail($id);

        DB::transaction(function () use ($settlement, $admin, $request) {
            $settlement->status = SettlementStatus::PAID;
            $settlement->bank_reference = $request->bank_reference;
            $settlement->paid_at = now();
            $settlement->save();

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'SETTLEMENT_PAID',
                'entity_type' => VendorSettlement::class,
                'entity_id' => $settlement->id,
                'old_values' => ['status' => SettlementStatus::ELIGIBLE->value],
                'new_values' => [
                    'status' => SettlementStatus::PAID->value,
                    'bank_reference' => $request->bank_reference,
                    'paid_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        $formattedPayout = number_format((float) $settlement->net_payout_amount, 0, ',', '.');

        return redirect()->route('admin.settlements.index')
            ->with('success', "Pencairan dana sebesar Rp {$formattedPayout} ke {$settlement->partner?->business_name} berhasil dicatat!");
    }
}


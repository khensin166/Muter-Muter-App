<?php

namespace App\Http\Controllers\Partner;

use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\VendorSettlement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerSettlementController extends Controller
{
    /**
     * Display the financial settlements ledger for the authenticated partner owner.
     */
    public function index(Request $request): Response
    {
        $partner = $request->user()->currentPartner();
        $this->authorize('viewSettlements', $partner);

        $statusFilter = $request->query('status', 'ALL');

        $query = VendorSettlement::where('partner_id', $partner->id)
            ->with(['booking.user']);

        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $settlements = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($s) {
            return [
                'id' => $s->id,
                'booking_code' => $s->booking?->booking_code,
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

        $totals = [
            'paid' => (float) VendorSettlement::where('partner_id', $partner->id)->where('status', SettlementStatus::PAID)->sum('net_payout_amount'),
            'eligible' => (float) VendorSettlement::where('partner_id', $partner->id)->where('status', SettlementStatus::ELIGIBLE)->sum('net_payout_amount'),
            'pending' => (float) VendorSettlement::where('partner_id', $partner->id)->where('status', SettlementStatus::PENDING)->sum('net_payout_amount'),
            'on_hold' => (float) VendorSettlement::where('partner_id', $partner->id)->where('status', SettlementStatus::ON_HOLD)->sum('net_payout_amount'),
        ];

        return Inertia::render('Partner/Settlements/Index', [
            'settlements' => $settlements,
            'totals' => $totals,
            'filters' => [
                'status' => $statusFilter,
            ],
            'partnerBank' => [
                'bank_name' => $partner->bank_name,
                'bank_account_number' => $partner->bank_account_number,
                'bank_account_holder' => $partner->bank_account_holder,
            ],
        ]);
    }
}


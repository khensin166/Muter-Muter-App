<?php

namespace App\Http\Controllers\Partner;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerDashboardController extends Controller
{
    /**
     * Display the Partner Dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $partner = $user->currentPartner();

        // 1. Incoming Booking Requests (SLA 24 Hours)
        $incomingBookings = Booking::where('partner_id', $partner->id)
            ->where('booking_status', BookingStatus::PENDING_APPROVAL)
            ->with(['product', 'user'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($b) {
                $slaRemainingSeconds = max(0, $b->vendor_sla_deadline->diffInSeconds(now(), false) * -1);
                $hours = floor($slaRemainingSeconds / 3600);
                $minutes = floor(($slaRemainingSeconds % 3600) / 60);

                return [
                    'id' => $b->id,
                    'code' => $b->booking_code,
                    'customer' => $b->user?->name,
                    'phone' => $b->user?->phone,
                    'product' => $b->product?->name,
                    'type' => $b->product?->product_type->value,
                    'dates' => $b->service_start_date->format('d M Y') . ($b->service_end_date ? ' - ' . $b->service_end_date->format('d M Y') : ''),
                    'totalPrice' => 'Rp ' . number_format($b->total_price, 0, ',', '.'),
                    'dpTarget' => 'Rp ' . number_format($b->dp_amount, 0, ',', '.') . ' (' . intval($b->commission_percentage) . '%)',
                    'slaRemaining' => sprintf('%02d:%02d:00', $hours, $minutes),
                    'slaStatus' => $hours < 6 ? 'URGENT' : 'NORMAL',
                ];
            });

        // 2. Metrics
        $pendingCount = $incomingBookings->count();
        $productsCount = Product::where('partner_id', $partner->id)->count();
        $confirmedCount = Booking::where('partner_id', $partner->id)
            ->whereIn('booking_status', [BookingStatus::CONFIRMED, BookingStatus::IN_PROGRESS])
            ->count();

        return Inertia::render('Partner/Dashboard', [
            'partner' => [
                'id' => $partner->id,
                'business_name' => $partner->business_name,
                'brand_name' => $partner->brand_name,
                'pic_name' => $partner->pic_name,
                'status' => $partner->status->value,
                'role' => $user->partnerRole()?->value,
                'base' => $partner->city,
                'bank_name' => $partner->bank_name,
                'bank_account_number' => $partner->bank_account_number,
                'bank_account_holder' => $partner->bank_account_holder,
            ],
            'metrics' => [
                'pendingCount' => $pendingCount,
                'productsCount' => $productsCount,
                'confirmedCount' => $confirmedCount,
                'readySettlement' => 'Rp 0',
            ],
            'incomingBookings' => $incomingBookings,
        ]);
    }
}


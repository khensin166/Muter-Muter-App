<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\PartnerStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\VendorSettlement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Central Operations Dashboard.
     */
    public function index(Request $request): Response
    {
        // 1. Metric Counts
        $metrics = [
            'pending_payments' => Payment::where('status', 'PAYMENT_REVIEW')->count(),
            'sla_breached' => Booking::where('booking_status', BookingStatus::MANUAL_REVIEW)->count(),
            'pending_partners' => Partner::where('status', PartnerStatus::PENDING)->count(),
            'pending_products' => Product::where('approval_status', ApprovalStatus::PENDING_REVIEW)->count(),
            'pending_refunds' => Refund::where('status', RefundStatus::PENDING)->count(),
            'eligible_payout_total' => (float) VendorSettlement::where('status', SettlementStatus::ELIGIBLE)->sum('net_payout_amount'),
            'total_commission_collected' => (float) VendorSettlement::whereIn('status', [SettlementStatus::ELIGIBLE, SettlementStatus::PAID])->sum('commission_amount'),
            'total_bookings' => Booking::count(),
        ];

        // 2. Urgent Attention Items (Payments to verify)
        $urgentPayments = Payment::with(['booking.user', 'booking.partner', 'latestProof'])
            ->where('status', 'PAYMENT_REVIEW')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'booking_code' => $p->booking?->booking_code,
                    'customer_name' => $p->booking?->user?->name,
                    'partner_name' => $p->booking?->partner?->business_name,
                    'amount' => (float) $p->amount,
                    'bank_sender' => $p->latestProof ? "{$p->latestProof->bank_sender_name} ({$p->latestProof->bank_sender_account})" : '-',
                    'created_at' => $p->created_at->format('d M Y, H:i'),
                ];
            });

        // 3. SLA Breached Bookings
        $urgentSla = Booking::with(['user', 'partner', 'product'])
            ->where('booking_status', BookingStatus::MANUAL_REVIEW)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'customer_name' => $b->user?->name,
                    'partner_name' => $b->partner?->business_name,
                    'partner_phone' => $b->partner?->pic_phone,
                    'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                    'total_price' => (float) $b->total_price,
                    'service_date' => $b->service_start_date->format('d M Y'),
                    'sla_deadline' => $b->vendor_sla_deadline?->format('d M Y, H:i'),
                ];
            });

        // 4. Recent Bookings Across Marketplace
        $recentBookings = Booking::with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'customer_name' => $b->user?->name,
                    'partner_name' => $b->partner?->business_name ?? 'Platform Internal',
                    'total_price' => (float) $b->total_price,
                    'dp_amount' => (float) $b->dp_amount,
                    'status' => $b->booking_status->value,
                    'status_label' => $b->booking_status->label(),
                    'created_at' => $b->created_at->format('d M Y, H:i'),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'urgentPayments' => $urgentPayments,
            'urgentSla' => $urgentSla,
            'recentBookings' => $recentBookings,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\PartnerStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\RefundStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
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
     * Display the Admin Central Operations & Master Catalog Dashboard.
     */
    public function index(Request $request): Response
    {
        // 1. Operational & Catalog Metric Counts
        $metrics = [
            'pending_payments' => Payment::where('status', 'PAYMENT_REVIEW')->count(),
            'sla_breached' => Booking::where('booking_status', BookingStatus::MANUAL_REVIEW)->count(),
            'pending_partners' => Partner::where('status', PartnerStatus::PENDING)->count(),
            'approved_partners' => Partner::where('status', PartnerStatus::APPROVED)->count(),
            'pending_products' => Product::where('approval_status', ApprovalStatus::PENDING_REVIEW)->count(),
            'pending_refunds' => Refund::where('status', RefundStatus::PENDING)->count(),
            'eligible_payout_total' => (float) VendorSettlement::where('status', SettlementStatus::ELIGIBLE)->sum('net_payout_amount'),
            'total_commission_collected' => (float) VendorSettlement::whereIn('status', [SettlementStatus::ELIGIBLE, SettlementStatus::PAID])->sum('commission_amount'),
            'total_bookings' => Booking::count(),
            'total_destinations' => Destination::where('is_active', true)->count(),
            'total_tours' => Product::where('product_type', ProductType::TOUR)->count(),
            'published_tours' => Product::where('product_type', ProductType::TOUR)->where('status', ProductStatus::PUBLISHED)->count(),
            'active_vehicles' => Product::where('product_type', ProductType::VEHICLE)->where('status', ProductStatus::PUBLISHED)->count(),
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
                    'partner_name' => $p->booking?->partner?->business_name ?? 'Platform Resmi (Curated)',
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
                    'partner_name' => $b->partner?->business_name ?? 'Belum Di-dispatch',
                    'partner_phone' => $b->partner?->pic_phone,
                    'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                    'total_price' => (float) $b->total_price,
                    'service_date' => $b->service_start_date->format('d M Y'),
                    'sla_deadline' => $b->vendor_sla_deadline?->format('d M Y, H:i'),
                ];
            });

        // 4. Recent Bookings Across Marketplace (with Dispatch Info)
        $recentBookings = Booking::with(['user', 'partner', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'customer_name' => $b->user?->name,
                    'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                    'partner_id' => $b->partner_id,
                    'partner_name' => $b->partner?->business_name ?? 'Menunggu Dispatch Mitra',
                    'assigned_driver_name' => $b->assigned_driver_name,
                    'assigned_vehicle_plate' => $b->assigned_vehicle_plate,
                    'total_price' => (float) $b->total_price,
                    'dp_amount' => (float) $b->dp_amount,
                    'status' => $b->booking_status->value,
                    'status_label' => $b->booking_status->label(),
                    'service_date' => $b->service_start_date->format('d M Y'),
                    'created_at' => $b->created_at->format('d M Y, H:i'),
                ];
            });

        // 5. Master Destinations Overview
        $featuredDestinations = Destination::with(['category', 'media'])
            ->withCount('itineraryStops')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get()
            ->map(function (Destination $d) {
                $primaryMedia = $d->media->firstWhere('is_primary', true) ?? $d->media->first();

                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'slug' => $d->slug,
                    'cluster_label' => $d->cluster->label(),
                    'category_name' => $d->category?->name ?? 'Alam & Budaya',
                    'ticket_price_domestic' => (float) $d->ticket_price_domestic,
                    'media_count' => $d->media->count(),
                    'stops_count' => $d->itinerary_stops_count,
                    'image_url' => $primaryMedia?->media_url ?? 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=600&q=80',
                ];
            });

        // 6. Tour Packages Overview (with Destination Stop Count)
        $featuredTours = Product::with(['tourPackage.itineraryDays.stops.destination', 'partner', 'media'])
            ->where('product_type', ProductType::TOUR)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(function (Product $p) {
                $stops = $p->tourPackage?->itineraryDays
                    ?->flatMap(fn ($day) => $day->stops) ?? collect();

                $boundDestinations = $stops->map(fn ($s) => $s->destination?->name)->filter()->unique()->values();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'ownership_type' => $p->ownership_type->value,
                    'partner_name' => $p->partner?->business_name ?? 'Platform Resmi (Super Admin)',
                    'base_price' => (float) $p->base_price,
                    'duration_days' => $p->tourPackage?->duration_days ?? 1,
                    'includes_ferry' => (bool) $p->tourPackage?->includes_ferry,
                    'status' => $p->status->value,
                    'approval_status' => $p->approval_status->value,
                    'stops_count' => $stops->count(),
                    'bound_destinations' => $boundDestinations,
                ];
            });

        // 7. Verified Partners for Dispatching Selector
        $availablePartners = Partner::where('status', PartnerStatus::APPROVED)
            ->orderByDesc('average_rating')
            ->orderByDesc('completed_trips_count')
            ->get()
            ->map(fn (Partner $p) => [
                'id' => $p->id,
                'business_name' => $p->business_name,
                'city' => $p->city,
                'average_rating' => (float) $p->average_rating,
                'completed_trips_count' => (int) $p->completed_trips_count,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'urgentPayments' => $urgentPayments,
            'urgentSla' => $urgentSla,
            'recentBookings' => $recentBookings,
            'featuredDestinations' => $featuredDestinations,
            'featuredTours' => $featuredTours,
            'availablePartners' => $availablePartners,
        ]);
    }
}

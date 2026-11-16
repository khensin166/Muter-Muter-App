<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\PartnerStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminBookingController extends Controller
{
    /**
     * Display a comprehensive listing of all marketplace bookings.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'ALL');
        $typeFilter = $request->query('type', 'ALL');
        $search = $request->query('search', '');

        $query = Booking::query()
            ->with(['user', 'product', 'partner', 'originalPartner', 'tourDetail', 'vehicleDetail']);

        // Filter by booking status
        if ($statusFilter !== 'ALL' && BookingStatus::tryFrom($statusFilter)) {
            $query->where('booking_status', $statusFilter);
        }

        // Filter by product type
        if ($typeFilter !== 'ALL' && in_array($typeFilter, ['TOUR', 'VEHICLE'])) {
            $query->whereHas('product', function ($q) use ($typeFilter) {
                $q->where('product_type', $typeFilter);
            });
        }

        // Keyword search (Booking code, customer name, customer email, partner name)
        if (!empty($search)) {
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('booking_code', $like, "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search, $like) {
                        $uq->where('name', $like, "%{$search}%")
                            ->orWhere('email', $like, "%{$search}%")
                            ->orWhere('phone', $like, "%{$search}%");
                    })
                    ->orWhereHas('partner', function ($pq) use ($search, $like) {
                        $pq->where('business_name', $like, "%{$search}%");
                    });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString()->through(function ($b) {
            $remainingHours = null;
            if (in_array($b->booking_status, [BookingStatus::PENDING_APPROVAL, BookingStatus::ASSIGNED_TO_PARTNER]) && $b->vendor_sla_deadline) {
                $remainingHours = round(now()->diffInMinutes($b->vendor_sla_deadline, false) / 60, 1);
            }

            return [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'customer' => [
                    'name' => $b->user?->name,
                    'phone' => $b->user?->phone,
                    'email' => $b->user?->email,
                ],
                'partner' => [
                    'id' => $b->partner_id,
                    'name' => $b->partner?->business_name ?? 'Platform Curated (Belum Di-dispatch)',
                    'city' => $b->partner?->city,
                    'phone' => $b->partner?->pic_phone,
                ],
                'original_partner_name' => $b->originalPartner?->business_name,
                'is_reassigned' => $b->original_partner_id && $b->original_partner_id !== $b->partner_id,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name ?? 'Produk Wisata',
                'product_type' => $b->product?->product_type?->value ?? ($b->product_snapshot['product_type'] ?? 'TOUR'),
                'service_start_date' => $b->service_start_date?->format('d M Y'),
                'service_end_date' => $b->service_end_date?->format('d M Y'),
                'total_price' => (float) $b->total_price,
                'dp_amount' => (float) $b->dp_amount,
                'total_paid' => (float) $b->total_paid,
                'booking_status' => $b->booking_status->value,
                'booking_status_label' => $b->booking_status->label(),
                'payment_status' => $b->payment_status->value,
                'inventory_status' => $b->inventory_status->value,
                'assigned_driver_name' => $b->assigned_driver_name,
                'assigned_vehicle_plate' => $b->assigned_vehicle_plate,
                'vendor_sla_deadline' => $b->vendor_sla_deadline?->toIso8601String(),
                'remaining_sla_hours' => $remainingHours,
                'is_sla_expired' => $remainingHours !== null && $remainingHours <= 0,
                'created_at' => $b->created_at->format('d M Y, H:i'),
            ];
        });

        // Global status counts for badges & fast tabs
        $counts = [
            'all' => Booking::count(),
            'pending_approval' => Booking::where('booking_status', BookingStatus::PENDING_APPROVAL)->count(),
            'assigned_to_partner' => Booking::where('booking_status', BookingStatus::ASSIGNED_TO_PARTNER)->count(),
            'awaiting_payment' => Booking::where('booking_status', BookingStatus::AWAITING_PAYMENT)->count(),
            'confirmed' => Booking::where('booking_status', BookingStatus::CONFIRMED)->count(),
            'manual_review' => Booking::where('booking_status', BookingStatus::MANUAL_REVIEW)->count(),
            'cancelled' => Booking::where('booking_status', BookingStatus::CANCELLED)->count(),
            'tour_count' => Booking::whereHas('product', fn ($q) => $q->where('product_type', ProductType::TOUR))->count(),
            'vehicle_count' => Booking::whereHas('product', fn ($q) => $q->where('product_type', ProductType::VEHICLE))->count(),
        ];

        // Approved verified partners list for reassign / dispatch modal
        $partners = Partner::where('status', PartnerStatus::APPROVED)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'city', 'pic_phone', 'average_rating']);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'counts' => $counts,
            'partners' => $partners,
            'filters' => [
                'status' => $statusFilter,
                'type' => $typeFilter,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display full booking detail and audit trace for operations admin.
     */
    public function show(Request $request, string $id): Response
    {
        $booking = Booking::with([
            'user',
            'product',
            'partner',
            'originalPartner',
            'tourDetail',
            'vehicleDetail',
            'statusHistories.actor',
            'payments.latestProof',
            'inventoryHolds',
            'settlement',
        ])->findOrFail($id);

        $remainingHours = null;
        if (in_array($booking->booking_status, [BookingStatus::PENDING_APPROVAL, BookingStatus::ASSIGNED_TO_PARTNER]) && $booking->vendor_sla_deadline) {
            $remainingHours = round(now()->diffInMinutes($booking->vendor_sla_deadline, false) / 60, 1);
        }

        $partners = Partner::where('status', PartnerStatus::APPROVED)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'city', 'pic_phone', 'average_rating']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer' => [
                    'id' => $booking->user?->id,
                    'name' => $booking->user?->name,
                    'email' => $booking->user?->email,
                    'phone' => $booking->user?->phone,
                ],
                'partner' => $booking->partner ? [
                    'id' => $booking->partner->id,
                    'name' => $booking->partner->business_name,
                    'city' => $booking->partner->city,
                    'phone' => $booking->partner->pic_phone,
                    'pic_name' => $booking->partner->pic_name,
                    'rating' => (float) $booking->partner->average_rating,
                ] : null,
                'original_partner' => $booking->originalPartner ? [
                    'id' => $booking->originalPartner->id,
                    'name' => $booking->originalPartner->business_name,
                    'city' => $booking->originalPartner->city,
                ] : null,
                'is_reassigned' => $booking->original_partner_id && $booking->original_partner_id !== $booking->partner_id,
                'rejection_reason' => $booking->rejection_reason,
                'assigned_driver_name' => $booking->assigned_driver_name,
                'assigned_driver_phone' => $booking->assigned_driver_phone,
                'assigned_vehicle_plate' => $booking->assigned_vehicle_plate,
                'product_snapshot' => $booking->product_snapshot,
                'pricing_breakdown' => $booking->pricing_breakdown,
                'cancellation_policy_snapshot' => $booking->cancellation_policy_snapshot,
                'booking_status' => $booking->booking_status->value,
                'booking_status_label' => $booking->booking_status->label(),
                'payment_status' => $booking->payment_status->value,
                'inventory_status' => $booking->inventory_status->value,
                'service_start_date' => $booking->service_start_date?->format('d M Y'),
                'service_end_date' => $booking->service_end_date?->format('d M Y'),
                'service_start_time' => $booking->service_start_time,
                'total_price' => (float) $booking->total_price,
                'dp_amount' => (float) $booking->dp_amount,
                'total_paid' => (float) $booking->total_paid,
                'commission_percentage' => (float) $booking->commission_percentage,
                'platform_commission_amount' => (float) $booking->platform_commission_amount,
                'partner_payout_amount' => (float) $booking->partner_payout_amount,
                'vendor_sla_deadline' => $booking->vendor_sla_deadline?->toIso8601String(),
                'remaining_sla_hours' => $remainingHours,
                'is_sla_expired' => $remainingHours !== null && $remainingHours <= 0,
                'tour_detail' => $booking->tourDetail ? [
                    'participant_count' => $booking->tourDetail->participant_count,
                    'special_requests' => $booking->tourDetail->special_requests,
                ] : null,
                'vehicle_detail' => $booking->vehicleDetail ? [
                    'pickup_location' => $booking->vehicleDetail->pickup_location,
                    'dropoff_location' => $booking->vehicleDetail->dropoff_location,
                    'driver_notes' => $booking->vehicleDetail->driver_notes,
                ] : null,
                'payments' => $booking->payments->map(fn ($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'status' => $p->status->value,
                    'payment_type' => $p->payment_type->value,
                    'created_at' => $p->created_at->format('d M Y, H:i'),
                    'proof_image_url' => $p->latestProof?->proof_file_path,
                    'bank_sender' => $p->latestProof ? "{$p->latestProof->bank_sender_name} ({$p->latestProof->bank_sender_account})" : null,
                ]),
                'histories' => $booking->statusHistories->map(fn ($h) => [
                    'id' => $h->id,
                    'actor_name' => $h->actor?->name ?? 'Sistem Muter-Muter',
                    'previous_status' => $h->previous_status,
                    'new_status' => $h->new_status,
                    'reason' => $h->reason,
                    'created_at' => $h->created_at->format('d M Y, H:i'),
                ]),
                'created_at' => $booking->created_at->format('d M Y, H:i'),
            ],
            'partners' => $partners,
        ]);
    }
}

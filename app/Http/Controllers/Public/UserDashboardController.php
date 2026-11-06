<?php

namespace App\Http\Controllers\Public;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    /**
     * Display the User/Customer Travel Hub Dashboard.
     * Redirects Admins and Partners to their respective specialized portals.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Role-based portals
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isPartner()) {
            return redirect()->route('partner.dashboard');
        }

        // Metrics for Customer
        $userBookings = Booking::where('user_id', $user->id);

        $metrics = [
            'active_trips' => (clone $userBookings)
                ->whereIn('booking_status', [BookingStatus::CONFIRMED, BookingStatus::IN_PROGRESS])
                ->where('service_start_date', '>=', now()->toDateString())
                ->count(),
            'pending_payment' => (clone $userBookings)
                ->whereIn('payment_status', [PaymentStatus::UNPAID, PaymentStatus::DOWN_PAYMENT_PENDING])
                ->whereNotIn('booking_status', [BookingStatus::CANCELLED, BookingStatus::EXPIRED, BookingStatus::REJECTED])
                ->count(),
            'completed_trips' => (clone $userBookings)
                ->where('booking_status', BookingStatus::COMPLETED)
                ->count(),
            'total_bookings' => (clone $userBookings)->count(),
        ];

        // Action Required: Bookings waiting for payment proof upload
        $actionRequired = Booking::where('user_id', $user->id)
            ->whereIn('payment_status', [PaymentStatus::UNPAID, PaymentStatus::DOWN_PAYMENT_PENDING])
            ->whereNotIn('booking_status', [BookingStatus::CANCELLED, BookingStatus::EXPIRED, BookingStatus::REJECTED])
            ->with(['product', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'dp_amount' => (float) $b->dp_amount,
                'total_price' => (float) $b->total_price,
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'payment_status' => $b->payment_status->value,
                'payment_status_label' => $b->payment_status->label(),
                'status' => $b->booking_status->value,
                'status_label' => $b->booking_status->label(),
            ]);

        // Upcoming Confirmed Trips
        $upcomingTrips = Booking::where('user_id', $user->id)
            ->whereIn('booking_status', [BookingStatus::CONFIRMED, BookingStatus::IN_PROGRESS])
            ->where('service_start_date', '>=', now()->toDateString())
            ->with(['product', 'partner'])
            ->orderBy('service_start_date', 'asc')
            ->limit(3)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'partner_name' => $b->partner?->business_name,
                'partner_phone' => $b->partner?->pic_phone,
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'service_start_time' => $b->service_start_time,
                'total_paid' => (float) $b->total_paid,
                'total_price' => (float) $b->total_price,
            ]);

        // Recent Bookings (All Statuses)
        $recentBookings = Booking::where('user_id', $user->id)
            ->with(['product', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'partner_name' => $b->partner?->business_name,
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'total_price' => (float) $b->total_price,
                'dp_amount' => (float) $b->dp_amount,
                'status' => $b->booking_status->value,
                'status_label' => $b->booking_status->label(),
                'payment_status' => $b->payment_status->value,
                'payment_status_label' => $b->payment_status->label(),
                'created_at' => $b->created_at->format('d M Y, H:i'),
            ]);

        // Recommended Destinations
        $featuredDestinations = Destination::where('is_featured', true)
            ->with('media')
            ->limit(4)
            ->get()
            ->map(fn ($d) => [
                'name' => $d->name,
                'slug' => $d->slug,
                'cluster' => $d->cluster->label(),
                'image' => $d->media->first()?->file_path ?? null,
                'ticket_price' => (float) $d->ticket_price_domestic,
            ]);

        // Recommended Tours
        $featuredTours = Product::where('status', ProductStatus::PUBLISHED)
            ->where('approval_status', ApprovalStatus::APPROVED)
            ->where('product_type', 'TOUR')
            ->with(['media', 'partner'])
            ->limit(3)
            ->get()
            ->map(fn ($t) => [
                'name' => $t->name,
                'slug' => $t->slug,
                'partner_name' => $t->partner?->business_name,
                'base_price' => (float) $t->base_price,
                'image' => $t->media->first()?->file_path ?? null,
            ]);

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'actionRequired' => $actionRequired,
            'upcomingTrips' => $upcomingTrips,
            'recentBookings' => $recentBookings,
            'featuredDestinations' => $featuredDestinations,
            'featuredTours' => $featuredTours,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductApprovalLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductModerationController extends Controller
{
    /**
     * Display a listing of products awaiting moderation or historical status.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'PENDING_REVIEW');
        $typeFilter = $request->query('type', 'ALL');

        $query = Product::query()->with(['partner', 'tourPackage', 'vehicle', 'media']);

        if ($statusFilter !== 'ALL') {
            $query->where('approval_status', $statusFilter);
        }

        if ($typeFilter !== 'ALL') {
            $query->where('product_type', $typeFilter);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'product_type' => $p->product_type->value,
                'partner_name' => $p->partner?->business_name,
                'partner_city' => $p->partner?->city,
                'base_price' => (float) $p->base_price,
                'pricing_type' => $p->pricing_type->value,
                'status' => $p->status->value,
                'approval_status' => $p->approval_status->value,
                'created_at' => $p->created_at->format('d M Y, H:i'),
                'media_url' => $p->media->first()?->media_url,
                'summary' => $p->product_type === ProductType::TOUR
                    ? ($p->tourPackage ? "{$p->tourPackage->duration_days}H{$p->tourPackage->duration_nights}M ({$p->tourPackage->tour_type->label()})" : '-')
                    : ($p->vehicle ? "{$p->vehicle->brand} {$p->vehicle->model} ({$p->vehicle->passenger_capacity} Kursi)" : '-'),
            ];
        });

        $counts = [
            'pending' => Product::where('approval_status', ApprovalStatus::PENDING_REVIEW)->count(),
            'approved' => Product::where('approval_status', ApprovalStatus::APPROVED)->count(),
            'rejected' => Product::where('approval_status', ApprovalStatus::REJECTED)->count(),
            'all' => Product::count(),
        ];

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'counts' => $counts,
            'filters' => [
                'status' => $statusFilter,
                'type' => $typeFilter,
            ],
        ]);
    }

    /**
     * Display the detailed moderation view of a product.
     */
    public function show(string $id): Response
    {
        $product = Product::query()
            ->with([
                'partner',
                'tourPackage.itineraryDays.stops.destination',
                'vehicle',
                'media',
                'approvalLogs.admin',
            ])
            ->findOrFail($id);

        $tour = $product->tourPackage;
        $vehicle = $product->vehicle;

        $itinerary = $tour?->itineraryDays->map(fn ($day) => [
            'id' => $day->id,
            'day_number' => $day->day_number,
            'title' => $day->title,
            'description' => $day->description,
            'stops' => $day->stops->map(fn ($stop) => [
                'id' => $stop->id,
                'stop_order' => $stop->stop_order,
                'title' => $stop->title,
                'activity_description' => $stop->activity_description,
                'estimated_minutes' => $stop->estimated_minutes,
                'destination_name' => $stop->destination?->name,
            ]),
        ]);

        return Inertia::render('Admin/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_type' => $product->product_type->value,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'base_price' => (float) $product->base_price,
                'pricing_type' => $product->pricing_type->value,
                'dp_percentage' => (float) $product->dp_percentage,
                'min_lead_time_hours' => $product->min_lead_time_hours,
                'status' => $product->status->value,
                'approval_status' => $product->approval_status->value,
                'created_at' => $product->created_at->format('d M Y, H:i'),
                'partner' => [
                    'id' => $product->partner?->id,
                    'business_name' => $product->partner?->business_name,
                    'pic_name' => $product->partner?->pic_name,
                    'pic_phone' => $product->partner?->pic_phone,
                    'city' => $product->partner?->city,
                    'status' => $product->partner?->status->value,
                ],
                'tour' => $tour ? [
                    'tour_type' => $tour->tour_type->label(),
                    'duration_days' => $tour->duration_days,
                    'duration_nights' => $tour->duration_nights,
                    'min_participants' => $tour->min_participants,
                    'max_participants' => $tour->max_participants,
                    'includes_ferry' => (bool) $tour->includes_ferry,
                    'inclusions' => $tour->inclusions,
                    'exclusions' => $tour->exclusions,
                    'terms_conditions' => $tour->terms_conditions,
                ] : null,
                'vehicle' => $vehicle ? [
                    'vehicle_type' => $vehicle->vehicle_type->label(),
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'manufacturing_year' => $vehicle->manufacturing_year,
                    'passenger_capacity' => $vehicle->passenger_capacity,
                    'luggage_capacity' => $vehicle->luggage_capacity,
                    'transmission' => $vehicle->transmission->label(),
                    'with_driver' => (bool) $vehicle->with_driver,
                    'license_plate' => $vehicle->license_plate,
                ] : null,
                'itinerary' => $itinerary,
                'media' => $product->media->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->media_url,
                    'is_primary' => $m->is_primary,
                ]),
                'logs' => $product->approvalLogs->map(fn ($log) => [
                    'id' => $log->id,
                    'admin_name' => $log->admin?->name,
                    'previous_status' => $log->previous_status,
                    'new_status' => $log->new_status,
                    'reason' => $log->reason,
                    'created_at' => $log->created_at->format('d M Y, H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Approve a product for public marketplace listing.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $admin = $request->user();
        $product = Product::findOrFail($id);

        $prevStatus = $product->approval_status->value;

        DB::transaction(function () use ($product, $admin, $prevStatus) {
            $product->approval_status = ApprovalStatus::APPROVED;
            $product->status = ProductStatus::PUBLISHED;
            $product->save();

            ProductApprovalLog::create([
                'product_id' => $product->id,
                'admin_id' => $admin->id,
                'previous_status' => $prevStatus,
                'new_status' => ApprovalStatus::APPROVED->value,
                'reason' => 'Disetujui untuk tayang di katalog publik marketplace.',
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'PRODUCT_APPROVED',
                'entity_type' => Product::class,
                'entity_id' => $product->id,
                'old_values' => ['approval_status' => $prevStatus],
                'new_values' => ['approval_status' => ApprovalStatus::APPROVED->value],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil disetujui dan kini aktif di katalog publik.");
    }

    /**
     * Reject a product with feedback reason.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $admin = $request->user();
        $product = Product::findOrFail($id);

        $prevStatus = $product->approval_status->value;

        DB::transaction(function () use ($product, $admin, $prevStatus, $request) {
            $product->approval_status = ApprovalStatus::REJECTED;
            $product->save();

            ProductApprovalLog::create([
                'product_id' => $product->id,
                'admin_id' => $admin->id,
                'previous_status' => $prevStatus,
                'new_status' => ApprovalStatus::REJECTED->value,
                'reason' => $request->reason,
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'PRODUCT_REJECTED',
                'entity_type' => Product::class,
                'entity_id' => $product->id,
                'old_values' => ['approval_status' => $prevStatus],
                'new_values' => [
                    'approval_status' => ApprovalStatus::REJECTED->value,
                    'reason' => $request->reason,
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Produk \"{$product->name}\" telah ditolak dengan catatan evaluasi.");
    }
}

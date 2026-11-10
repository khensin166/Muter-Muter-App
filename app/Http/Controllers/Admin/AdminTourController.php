<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Destination;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\TourItineraryDay;
use App\Models\TourItineraryStop;
use App\Models\TourPackage;
use App\Services\FileSecurityService;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminTourController extends Controller
{
    /**
     * Show the Super Admin Platform-Curated Tour Package Creator.
     */
    public function create(Request $request): Response
    {
        $destinations = Destination::with('media')
            ->where('is_active', true)
            ->orderBy('cluster')
            ->orderBy('name')
            ->get()
            ->map(function (Destination $d) {
                $primaryMedia = $d->media->firstWhere('is_primary', true) ?? $d->media->first();

                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'slug' => $d->slug,
                    'cluster' => $d->cluster->label(),
                    'cluster_code' => $d->cluster->value,
                    'short_description' => $d->short_description,
                    'ticket_price_domestic' => (float) $d->ticket_price_domestic,
                    'image_url' => $primaryMedia?->media_url ?? 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=800&q=80',
                    'media_count' => $d->media->count(),
                ];
            });

        $tourTypes = collect(TourType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        $partners = Partner::where('status', PartnerStatus::APPROVED)
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

        return Inertia::render('Admin/Products/CreateTour', [
            'destinations' => $destinations,
            'tourTypes' => $tourTypes,
            'partners' => $partners,
        ]);
    }

    /**
     * Store a newly created Platform-Curated Tour Package.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:350'],
            'full_description' => ['required', 'string'],
            'tour_type' => ['required', 'string', 'in:' . implode(',', array_column(TourType::cases(), 'value'))],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'pricing_type' => ['required', 'string', 'in:' . implode(',', array_column(PricingType::cases(), 'value'))],
            'dp_percentage' => ['nullable', 'numeric', 'min:10', 'max:100'],
            'partner_id' => ['nullable', 'uuid', 'exists:partners,id'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'duration_nights' => ['required', 'integer', 'min:0', 'max:30'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'min:1', 'gte:min_participants'],
            'includes_ferry' => ['required', 'boolean'],
            'inclusions' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'itinerary' => ['nullable', 'array'],
            'itinerary.*.day_number' => ['required_with:itinerary', 'integer'],
            'itinerary.*.title' => ['required_with:itinerary', 'string', 'max:255'],
            'itinerary.*.description' => ['nullable', 'string'],
            'itinerary.*.stops' => ['nullable', 'array'],
            'itinerary.*.stops.*.title' => ['required', 'string'],
            'itinerary.*.stops.*.destination_id' => ['nullable', 'uuid', 'exists:destinations,id'],
            'itinerary.*.stops.*.activity_description' => ['nullable', 'string'],
            'itinerary.*.stops.*.estimated_minutes' => ['nullable', 'integer'],
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $idx => $photo) {
                $photoPaths[] = '/storage/' . FileSecurityService::validateAndStore(
                    file: $photo,
                    directory: 'products/tours',
                    fieldName: "photos.{$idx}",
                    allowPdf: false,
                    disk: 'public'
                );
            }
        }

        $translator = app(TranslationService::class);

        $nameEn = $request->input('name_en') ?: $translator->translate($validated['name'], 'en', 'id');
        $shortDescEn = $request->input('short_description_en') ?: $translator->translate($validated['short_description'], 'en', 'id');
        $fullDescEn = $request->input('full_description_en') ?: $translator->translate($validated['full_description'], 'en', 'id');

        $namePayload = ['id' => $validated['name'], 'en' => $nameEn];
        $shortDescPayload = ['id' => $validated['short_description'], 'en' => $shortDescEn];
        $fullDescPayload = ['id' => $validated['full_description'], 'en' => $fullDescEn];

        $inclusionsEn = $request->input('inclusions_en') ?: (!empty($validated['inclusions']) ? $translator->translate($validated['inclusions'], 'en', 'id') : null);
        $inclusionsPayload = !empty($validated['inclusions']) ? ['id' => $validated['inclusions'], 'en' => $inclusionsEn] : null;

        $exclusionsEn = $request->input('exclusions_en') ?: (!empty($validated['exclusions']) ? $translator->translate($validated['exclusions'], 'en', 'id') : null);
        $exclusionsPayload = !empty($validated['exclusions']) ? ['id' => $validated['exclusions'], 'en' => $exclusionsEn] : null;

        $termsEn = $request->input('terms_conditions_en') ?: (!empty($validated['terms_conditions']) ? $translator->translate($validated['terms_conditions'], 'en', 'id') : null);
        $termsPayload = !empty($validated['terms_conditions']) ? ['id' => $validated['terms_conditions'], 'en' => $termsEn] : null;

        DB::transaction(function () use ($validated, $photoPaths, $namePayload, $shortDescPayload, $fullDescPayload, $inclusionsPayload, $exclusionsPayload, $termsPayload, $translator) {
            $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(5));

            $product = Product::create([
                'partner_id' => $validated['partner_id'] ?? null,
                'ownership_type' => OwnershipType::INTERNAL,
                'product_type' => ProductType::TOUR,
                'name' => $namePayload,
                'slug' => $slug,
                'short_description' => $shortDescPayload,
                'full_description' => $fullDescPayload,
                'base_price' => $validated['base_price'],
                'pricing_type' => $validated['pricing_type'],
                'dp_percentage' => $validated['dp_percentage'] ?? 30.00,
                'min_lead_time_hours' => 24,
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::APPROVED,
            ]);

            $tourPackage = TourPackage::create([
                'product_id' => $product->id,
                'tour_type' => $validated['tour_type'],
                'duration_days' => $validated['duration_days'],
                'duration_nights' => $validated['duration_nights'],
                'min_participants' => $validated['min_participants'],
                'max_participants' => $validated['max_participants'],
                'inclusions' => $inclusionsPayload,
                'exclusions' => $exclusionsPayload,
                'terms_conditions' => $termsPayload,
                'includes_ferry' => $validated['includes_ferry'],
            ]);

            $boundDestinationIds = [];

            if (!empty($validated['itinerary'])) {
                foreach ($validated['itinerary'] as $dayData) {
                    $dayTitleEn = $dayData['title_en'] ?? $translator->translate($dayData['title'], 'en', 'id');
                    $dayDescEn = !empty($dayData['description']) ? ($dayData['description_en'] ?? $translator->translate($dayData['description'], 'en', 'id')) : null;

                    $day = TourItineraryDay::create([
                        'tour_package_id' => $tourPackage->id,
                        'day_number' => $dayData['day_number'],
                        'title' => ['id' => $dayData['title'], 'en' => $dayTitleEn],
                        'description' => !empty($dayData['description']) ? ['id' => $dayData['description'], 'en' => $dayDescEn] : null,
                    ]);

                    if (!empty($dayData['stops'])) {
                        foreach ($dayData['stops'] as $index => $stopData) {
                            $destId = !empty($stopData['destination_id']) ? $stopData['destination_id'] : null;
                            if ($destId) {
                                $boundDestinationIds[] = $destId;
                            }

                            $stopTitleEn = $stopData['title_en'] ?? $translator->translate($stopData['title'], 'en', 'id');
                            $stopActEn = !empty($stopData['activity_description']) ? ($stopData['activity_description_en'] ?? $translator->translate($stopData['activity_description'], 'en', 'id')) : null;

                            TourItineraryStop::create([
                                'tour_itinerary_day_id' => $day->id,
                                'destination_id' => $destId,
                                'stop_order' => $index + 1,
                                'title' => ['id' => $stopData['title'], 'en' => $stopTitleEn],
                                'activity_description' => !empty($stopData['activity_description']) ? ['id' => $stopData['activity_description'], 'en' => $stopActEn] : null,
                                'estimated_minutes' => $stopData['estimated_minutes'] ?? 60,
                            ]);
                        }
                    }
                }
            }

            // If Admin did not upload custom photos, auto-bind primary photo from first bound destination
            if (empty($photoPaths) && !empty($boundDestinationIds)) {
                $firstDest = Destination::with('media')->find($boundDestinationIds[0]);
                $destMedia = $firstDest?->media->firstWhere('is_featured', true) ?? $firstDest?->media->first();
                if ($destMedia) {
                    $photoPaths[] = $destMedia->media_url;
                }
            }

            foreach ($photoPaths as $idx => $path) {
                ProductMedia::create([
                    'product_id' => $product->id,
                    'file_path' => $path,
                    'caption' => $validated['name'],
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx + 1,
                ]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Paket Wisata Resmi Platform berhasil dibuat dan langsung ditayangkan di katalog publik.');
    }

    /**
     * Dispatch a booking to a verified local Partner & assign driver/fleet.
     */
    public function dispatchBooking(Request $request, string $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'assigned_driver_name' => ['nullable', 'string', 'max:150'],
            'assigned_driver_phone' => ['nullable', 'string', 'max:50'],
            'assigned_vehicle_plate' => ['nullable', 'string', 'max:50'],
        ]);

        $partner = Partner::findOrFail($validated['partner_id']);

        DB::transaction(function () use ($booking, $partner, $validated, $request) {
            $oldPartnerId = $booking->partner_id;

            $booking->update([
                'partner_id' => $partner->id,
                'assigned_driver_name' => $validated['assigned_driver_name'] ?? $booking->assigned_driver_name,
                'assigned_driver_phone' => $validated['assigned_driver_phone'] ?? $booking->assigned_driver_phone,
                'assigned_vehicle_plate' => $validated['assigned_vehicle_plate'] ?? $booking->assigned_vehicle_plate,
                'vendor_sla_deadline' => now()->addHours(24),
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $request->user()->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $booking->booking_status->value,
                'new_status' => $booking->booking_status->value,
                'reason' => "Pesanan di-dispatch oleh Super Admin ke Mitra: {$partner->business_name} (★ {$partner->average_rating})",
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'BOOKING_DISPATCHED_TO_PARTNER',
                'entity_type' => Booking::class,
                'entity_id' => $booking->id,
                'old_values' => ['partner_id' => $oldPartnerId],
                'new_values' => [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->business_name,
                    'assigned_driver_name' => $validated['assigned_driver_name'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', "Pesanan {$booking->booking_code} berhasil di-dispatch ke mitra {$partner->business_name}.");
    }
}


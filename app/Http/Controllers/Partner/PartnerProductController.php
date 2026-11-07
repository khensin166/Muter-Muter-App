<?php

namespace App\Http\Controllers\Partner;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\TourItineraryDay;
use App\Models\TourItineraryStop;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Services\FileSecurityService;
use App\Services\GoogleTranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PartnerProductController extends Controller
{
    /**
     * Display a listing of partner products.
     */
    public function index(Request $request): Response
    {
        $partner = $request->user()->currentPartner();
        $type = $request->query('type', 'ALL');

        $query = Product::where('partner_id', $partner->id)
            ->with(['tourPackage', 'vehicle', 'media']);

        if ($type === 'TOUR') {
            $query->where('product_type', ProductType::TOUR);
        } elseif ($type === 'VEHICLE') {
            $query->where('product_type', ProductType::VEHICLE);
        }

        $products = $query->orderBy('created_at', 'desc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'product_type' => $p->product_type->value,
                'base_price' => (float) $p->base_price,
                'pricing_type' => $p->pricing_type->value,
                'status' => $p->status->value,
                'approval_status' => $p->approval_status->value,
                'min_lead_time_hours' => $p->min_lead_time_hours,
                'dp_percentage' => (float) $p->dp_percentage,
                'created_at' => $p->created_at->format('d M Y'),
                'media_url' => $p->media->first()?->media_url,
                'details' => $p->product_type === ProductType::TOUR ? [
                    'duration_days' => $p->tourPackage?->duration_days,
                    'tour_type' => $p->tourPackage?->tour_type?->label(),
                    'includes_ferry' => (bool) $p->tourPackage?->includes_ferry,
                ] : [
                    'brand' => $p->vehicle?->brand,
                    'model' => $p->vehicle?->model,
                    'passenger_capacity' => $p->vehicle?->passenger_capacity,
                    'with_driver' => (bool) $p->vehicle?->with_driver,
                ],
            ];
        });

        $counts = [
            'all' => Product::where('partner_id', $partner->id)->count(),
            'tours' => Product::where('partner_id', $partner->id)->where('product_type', ProductType::TOUR)->count(),
            'vehicles' => Product::where('partner_id', $partner->id)->where('product_type', ProductType::VEHICLE)->count(),
            'pending_approval' => Product::where('partner_id', $partner->id)->where('approval_status', ApprovalStatus::PENDING_REVIEW)->count(),
        ];

        return Inertia::render('Partner/Products/Index', [
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->business_name,
            ],
            'products' => $products,
            'counts' => $counts,
            'currentFilter' => $type,
        ]);
    }

    /**
     * Show the form for creating a new tour package.
     */
    public function createTour(Request $request): Response
    {
        $destinations = Destination::orderBy('name')->get(['id', 'name', 'cluster'])->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'cluster' => $d->cluster->label(),
        ]);

        $tourTypes = collect(TourType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        return Inertia::render('Partner/Products/CreateTour', [
            'destinations' => $destinations,
            'tourTypes' => $tourTypes,
        ]);
    }

    /**
     * Store a newly created tour package.
     */
    public function storeTour(Request $request): RedirectResponse
    {
        $partner = $request->user()->currentPartner();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:300'],
            'full_description' => ['required', 'string'],
            'tour_type' => ['required', 'string', 'in:'.implode(',', array_column(TourType::cases(), 'value'))],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'pricing_type' => ['required', 'string', 'in:'.implode(',', array_column(PricingType::cases(), 'value'))],
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

        // Deep 4-layer file inspection & sanitization before DB transaction
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $idx => $photo) {
                $photoPaths[] = FileSecurityService::validateAndStore(
                    file: $photo,
                    directory: 'products/tours',
                    fieldName: "photos.{$idx}",
                    allowPdf: false,
                    disk: 'public'
                );
            }
        }

        $translator = app(GoogleTranslationService::class);

        $nameEn = $request->input('name_en') ?: $translator->translate($validated['name'], 'en', 'id');
        $shortDescEn = $request->input('short_description_en') ?: $translator->translate($validated['short_description'], 'en', 'id');
        $fullDescEn = $request->input('full_description_en') ?: $translator->translate($validated['full_description'], 'en', 'id');

        $namePayload = ['id' => $validated['name'], 'en' => $nameEn];
        $shortDescPayload = ['id' => $validated['short_description'], 'en' => $shortDescEn];
        $fullDescPayload = ['id' => $validated['full_description'], 'en' => $fullDescEn];

        $inclusionsEn = $request->input('inclusions_en') ?: (! empty($validated['inclusions']) ? $translator->translate($validated['inclusions'], 'en', 'id') : null);
        $inclusionsPayload = ! empty($validated['inclusions']) ? ['id' => $validated['inclusions'], 'en' => $inclusionsEn] : null;

        $exclusionsEn = $request->input('exclusions_en') ?: (! empty($validated['exclusions']) ? $translator->translate($validated['exclusions'], 'en', 'id') : null);
        $exclusionsPayload = ! empty($validated['exclusions']) ? ['id' => $validated['exclusions'], 'en' => $exclusionsEn] : null;

        $termsEn = $request->input('terms_conditions_en') ?: (! empty($validated['terms_conditions']) ? $translator->translate($validated['terms_conditions'], 'en', 'id') : null);
        $termsPayload = ! empty($validated['terms_conditions']) ? ['id' => $validated['terms_conditions'], 'en' => $termsEn] : null;

        DB::transaction(function () use ($partner, $validated, $photoPaths, $namePayload, $shortDescPayload, $fullDescPayload, $inclusionsPayload, $exclusionsPayload, $termsPayload, $translator) {
            $slug = Str::slug($validated['name']).'-'.Str::random(6);

            $product = Product::create([
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::TOUR,
                'name' => $namePayload,
                'slug' => $slug,
                'short_description' => $shortDescPayload,
                'full_description' => $fullDescPayload,
                'base_price' => $validated['base_price'],
                'pricing_type' => $validated['pricing_type'],
                'dp_percentage' => 30.00, // Default MVP 30%
                'min_lead_time_hours' => 24, // H-1 strict requirement
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::PENDING_REVIEW, // Needs admin approval before visible in public catalog
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

            // Save Itinerary
            if (! empty($validated['itinerary'])) {
                foreach ($validated['itinerary'] as $dayData) {
                    $dayTitleEn = $dayData['title_en'] ?? $translator->translate($dayData['title'], 'en', 'id');
                    $dayDescEn = ! empty($dayData['description']) ? ($dayData['description_en'] ?? $translator->translate($dayData['description'], 'en', 'id')) : null;

                    $day = TourItineraryDay::create([
                        'tour_package_id' => $tourPackage->id,
                        'day_number' => $dayData['day_number'],
                        'title' => ['id' => $dayData['title'], 'en' => $dayTitleEn],
                        'description' => ! empty($dayData['description']) ? ['id' => $dayData['description'], 'en' => $dayDescEn] : null,
                    ]);

                    if (! empty($dayData['stops'])) {
                        foreach ($dayData['stops'] as $index => $stopData) {
                            $stopTitleEn = $stopData['title_en'] ?? $translator->translate($stopData['title'], 'en', 'id');
                            $stopActEn = ! empty($stopData['activity_description']) ? ($stopData['activity_description_en'] ?? $translator->translate($stopData['activity_description'], 'en', 'id')) : null;

                            TourItineraryStop::create([
                                'tour_itinerary_day_id' => $day->id,
                                'destination_id' => $stopData['destination_id'] ?? null,
                                'stop_order' => $index + 1,
                                'title' => ['id' => $stopData['title'], 'en' => $stopTitleEn],
                                'activity_description' => ! empty($stopData['activity_description']) ? ['id' => $stopData['activity_description'], 'en' => $stopActEn] : null,
                                'estimated_minutes' => $stopData['estimated_minutes'] ?? 60,
                            ]);
                        }
                    }
                }
            }

            // Save Photos
            foreach ($photoPaths as $idx => $path) {
                ProductMedia::create([
                    'product_id' => $product->id,
                    'media_url' => '/storage/'.$path,
                    'media_type' => 'IMAGE',
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx,
                ]);
            }
        });

        return redirect()->route('partner.products.index')
            ->with('success', 'Paket tour berhasil dibuat dan menunggu moderasi admin.');
    }

    /**
     * Show the form for creating a new vehicle rental.
     */
    public function createVehicle(Request $request): Response
    {
        $vehicleTypes = collect(VehicleType::cases())->map(fn ($v) => [
            'value' => $v->value,
            'label' => $v->label(),
        ]);

        $transmissions = collect(TransmissionType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        return Inertia::render('Partner/Products/CreateVehicle', [
            'vehicleTypes' => $vehicleTypes,
            'transmissions' => $transmissions,
        ]);
    }

    /**
     * Store a newly created vehicle rental.
     */
    public function storeVehicle(Request $request): RedirectResponse
    {
        $partner = $request->user()->currentPartner();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:300'],
            'full_description' => ['required', 'string'],
            'vehicle_type' => ['required', 'string', 'in:'.implode(',', array_column(VehicleType::cases(), 'value'))],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'manufacturing_year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'passenger_capacity' => ['required', 'integer', 'min:1', 'max:60'],
            'luggage_capacity' => ['required', 'integer', 'min:0', 'max:50'],
            'transmission' => ['required', 'string', 'in:'.implode(',', array_column(TransmissionType::cases(), 'value'))],
            'with_driver' => ['required', 'boolean'],
            'license_plate' => ['required', 'string', 'max:20'],
            'base_price' => ['required', 'numeric', 'min:50000'],
            'pricing_type' => ['required', 'string', 'in:'.implode(',', array_column(PricingType::cases(), 'value'))],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Deep 4-layer file inspection & sanitization before DB transaction
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $idx => $photo) {
                $photoPaths[] = FileSecurityService::validateAndStore(
                    file: $photo,
                    directory: 'products/vehicles',
                    fieldName: "photos.{$idx}",
                    allowPdf: false,
                    disk: 'public'
                );
            }
        }

        $translator = app(GoogleTranslationService::class);
        $nameEn = $request->input('name_en') ?: $translator->translate($validated['name'], 'en', 'id');
        $shortDescEn = $request->input('short_description_en') ?: $translator->translate($validated['short_description'], 'en', 'id');
        $fullDescEn = $request->input('full_description_en') ?: $translator->translate($validated['full_description'], 'en', 'id');

        $namePayload = ['id' => $validated['name'], 'en' => $nameEn];
        $shortDescPayload = ['id' => $validated['short_description'], 'en' => $shortDescEn];
        $fullDescPayload = ['id' => $validated['full_description'], 'en' => $fullDescEn];

        DB::transaction(function () use ($partner, $validated, $photoPaths, $namePayload, $shortDescPayload, $fullDescPayload) {
            $slug = Str::slug($validated['name']).'-'.Str::random(6);

            $product = Product::create([
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::VEHICLE,
                'name' => $namePayload,
                'slug' => $slug,
                'short_description' => $shortDescPayload,
                'full_description' => $fullDescPayload,
                'base_price' => $validated['base_price'],
                'pricing_type' => $validated['pricing_type'],
                'dp_percentage' => 30.00,
                'min_lead_time_hours' => 12, // H-12 Jam strict requirement
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::PENDING_REVIEW,
            ]);

            Vehicle::create([
                'product_id' => $product->id,
                'vehicle_type' => $validated['vehicle_type'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'manufacturing_year' => $validated['manufacturing_year'],
                'passenger_capacity' => $validated['passenger_capacity'],
                'luggage_capacity' => $validated['luggage_capacity'],
                'transmission' => $validated['transmission'],
                'with_driver' => $validated['with_driver'],
                'license_plate' => strtoupper($validated['license_plate']),
            ]);

            foreach ($photoPaths as $idx => $path) {
                ProductMedia::create([
                    'product_id' => $product->id,
                    'media_url' => '/storage/'.$path,
                    'media_type' => 'IMAGE',
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx,
                ]);
            }
        });

        return redirect()->route('partner.products.index')
            ->with('success', 'Unit armada berhasil didaftarkan dan menunggu moderasi admin.');
    }

    /**
     * Toggle product published status.
     */
    public function toggleStatus(Request $request, string $id): RedirectResponse
    {
        $partner = $request->user()->currentPartner();
        $product = Product::where('partner_id', $partner->id)->where('id', $id)->firstOrFail();

        $product->status = $product->status === ProductStatus::PUBLISHED
            ? ProductStatus::ARCHIVED
            : ProductStatus::PUBLISHED;

        $product->save();

        return back()->with('success', "Status produk {$product->name} berhasil diperbarui.");
    }
}

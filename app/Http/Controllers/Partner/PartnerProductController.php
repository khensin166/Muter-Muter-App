<?php

namespace App\Http\Controllers\Partner;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Vehicle;
use App\Services\FileSecurityService;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PartnerProductController extends Controller
{
    /**
     * Display a listing of partner fleet products (vehicles).
     */
    public function index(Request $request): Response
    {
        $partner = $request->user()->currentPartner();

        $query = Product::where('partner_id', $partner->id)
            ->where('product_type', ProductType::VEHICLE)
            ->with(['vehicle', 'media']);

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
                'details' => [
                    'brand' => $p->vehicle?->brand,
                    'model' => $p->vehicle?->model,
                    'passenger_capacity' => $p->vehicle?->passenger_capacity,
                    'with_driver' => (bool) $p->vehicle?->with_driver,
                ],
            ];
        });

        $counts = [
            'all' => Product::where('partner_id', $partner->id)->where('product_type', ProductType::VEHICLE)->count(),
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
            'currentFilter' => 'VEHICLE',
        ]);
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
            'base_price' => ['nullable', 'numeric', 'min:50000'],
            'pricing_type' => ['nullable', 'string', 'in:'.implode(',', array_column(PricingType::cases(), 'value'))],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Standardized Platform Tier Pricing (Admin-governed baseline rate)
        $standardPlatformRate = match ($validated['vehicle_type']) {
            VehicleType::MINIBUS->value => 1350000,
            VehicleType::BUS->value => 2500000,
            VehicleType::BOAT->value => 1500000,
            default => ((int) $validated['passenger_capacity'] <= 5 ? 650000 : 850000),
        };
        $validated['base_price'] = $validated['base_price'] ?? $standardPlatformRate;
        $validated['pricing_type'] = $validated['pricing_type'] ?? PricingType::PER_VEHICLE->value;

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

        $translator = app(TranslationService::class);
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

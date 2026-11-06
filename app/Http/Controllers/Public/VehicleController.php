<?php

namespace App\Http\Controllers\Public;

use App\Enums\ProductType;
use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicle rentals.
     */
    public function index(Request $request): Response
    {
        $vehicleType = $request->query('vehicle_type');
        $transmission = $request->query('transmission');
        $withDriver = $request->query('with_driver');

        $query = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::VEHICLE)
            ->with(['vehicle', 'partner', 'media']);

        if ($vehicleType && in_array($vehicleType, array_column(VehicleType::cases(), 'value'))) {
            $query->whereHas('vehicle', fn ($q) => $q->where('vehicle_type', $vehicleType));
        }

        if ($transmission && in_array($transmission, array_column(TransmissionType::cases(), 'value'))) {
            $query->whereHas('vehicle', fn ($q) => $q->where('transmission', $transmission));
        }

        if ($withDriver !== null && $withDriver !== '') {
            $query->whereHas('vehicle', fn ($q) => $q->where('with_driver', filter_var($withDriver, FILTER_VALIDATE_BOOLEAN)));
        }

        $vehicles = $query->orderBy('base_price', 'asc')->paginate(9)->through(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'short_description' => $p->short_description,
            'base_price' => (float) $p->base_price,
            'pricing_type' => $p->pricing_type->value,
            'dp_percentage' => (float) $p->dp_percentage,
            'min_lead_time_hours' => $p->min_lead_time_hours,
            'partner_name' => $p->partner?->business_name,
            'vehicle_type' => $p->vehicle?->vehicle_type?->label() ?? 'Mobil',
            'brand' => $p->vehicle?->brand,
            'model' => $p->vehicle?->model,
            'year' => $p->vehicle?->manufacturing_year,
            'passenger_capacity' => $p->vehicle?->passenger_capacity,
            'luggage_capacity' => $p->vehicle?->luggage_capacity,
            'transmission' => $p->vehicle?->transmission?->label() ?? 'Manual',
            'with_driver' => (bool) $p->vehicle?->with_driver,
            'image_url' => $p->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
        ]);

        $vehicleTypes = collect(VehicleType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        $transmissions = collect(TransmissionType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'vehicleTypes' => $vehicleTypes,
            'transmissions' => $transmissions,
            'filters' => [
                'vehicle_type' => $vehicleType,
                'transmission' => $transmission,
                'with_driver' => $withDriver,
            ],
        ]);
    }

    /**
     * Display a specific vehicle rental detail.
     */
    public function show(string $slug): Response
    {
        $product = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::VEHICLE)
            ->where('slug', $slug)
            ->with(['vehicle', 'partner', 'media'])
            ->firstOrFail();

        $veh = $product->vehicle;

        return Inertia::render('Vehicles/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'base_price' => (float) $product->base_price,
                'pricing_type' => $product->pricing_type->value,
                'dp_percentage' => (float) $product->dp_percentage,
                'min_lead_time_hours' => $product->min_lead_time_hours,
                'partner' => [
                    'id' => $product->partner?->id,
                    'name' => $product->partner?->business_name,
                    'city' => $product->partner?->city,
                ],
                'vehicle' => [
                    'id' => $veh?->id,
                    'vehicle_type' => $veh?->vehicle_type?->label(),
                    'brand' => $veh?->brand,
                    'model' => $veh?->model,
                    'manufacturing_year' => $veh?->manufacturing_year,
                    'passenger_capacity' => $veh?->passenger_capacity,
                    'luggage_capacity' => $veh?->luggage_capacity,
                    'transmission' => $veh?->transmission?->label(),
                    'with_driver' => (bool) $veh?->with_driver,
                    'license_plate' => $veh?->license_plate,
                ],
                'media' => $product->media->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->media_url,
                    'is_primary' => $m->is_primary,
                ]),
            ],
        ]);
    }
}


<?php

namespace App\Http\Controllers\Public;

use App\Enums\DestinationCluster;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the Lake Toba Marketplace Homepage.
     */
    public function __invoke(Request $request): Response
    {
        $clusters = collect(DestinationCluster::cases())->map(fn ($cluster) => [
            'value' => $cluster->value,
            'label' => $cluster->label(),
        ]);

        $featuredDestinations = Destination::query()
            ->with(['category', 'media'])
            ->where('is_featured', true)
            ->take(6)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'slug' => $d->slug,
                'cluster' => $d->cluster->value,
                'cluster_label' => $d->cluster->label(),
                'category' => $d->category?->name,
                'ticket_price_domestic' => (float) $d->ticket_price_domestic,
                'operating_hours' => $d->operating_hours,
                'image_url' => $d->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1578469550956-0e16b69c6a3d?auto=format&fit=crop&w=800&q=80',
            ]);

        $featuredTours = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::TOUR)
            ->with(['tourPackage', 'partner', 'media'])
            ->take(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'short_description' => $p->short_description,
                'base_price' => (float) $p->base_price,
                'pricing_type' => $p->pricing_type->value,
                'dp_percentage' => (float) $p->dp_percentage,
                'min_lead_time_hours' => $p->min_lead_time_hours,
                'partner_name' => $p->partner?->business_name,
                'duration_days' => $p->tourPackage?->duration_days ?? 1,
                'duration_nights' => $p->tourPackage?->duration_nights ?? 0,
                'tour_type' => $p->tourPackage?->tour_type?->label() ?? 'Tour',
                'includes_ferry' => (bool) $p->tourPackage?->includes_ferry,
                'image_url' => $p->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
            ]);

        $featuredVehicles = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::VEHICLE)
            ->with(['vehicle', 'partner', 'media'])
            ->take(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'short_description' => $p->short_description,
                'base_price' => (float) $p->base_price,
                'pricing_type' => $p->pricing_type->value,
                'dp_percentage' => (float) $p->dp_percentage,
                'min_lead_time_hours' => $p->min_lead_time_hours,
                'partner_name' => $p->partner?->business_name,
                'brand' => $p->vehicle?->brand,
                'model' => $p->vehicle?->model,
                'year' => $p->vehicle?->manufacturing_year,
                'passenger_capacity' => $p->vehicle?->passenger_capacity,
                'luggage_capacity' => $p->vehicle?->luggage_capacity,
                'transmission' => $p->vehicle?->transmission?->label() ?? 'Manual',
                'with_driver' => (bool) $p->vehicle?->with_driver,
                'image_url' => $p->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
            ]);

        return Inertia::render('Welcome', [
            'clusters' => $clusters,
            'featuredDestinations' => $featuredDestinations,
            'featuredTours' => $featuredTours,
            'featuredVehicles' => $featuredVehicles,
        ]);
    }
}


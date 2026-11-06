<?php

namespace App\Http\Controllers\Public;

use App\Enums\ProductType;
use App\Enums\TourType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourController extends Controller
{
    /**
     * Display a listing of tour packages.
     */
    public function index(Request $request): Response
    {
        $tourType = $request->query('tour_type');
        $search = $request->query('search');

        $query = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::TOUR)
            ->with(['tourPackage', 'partner', 'media']);

        if ($tourType && in_array($tourType, array_column(TourType::cases(), 'value'))) {
            $query->whereHas('tourPackage', fn ($q) => $q->where('tour_type', $tourType));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('short_description', 'ilike', "%{$search}%");
            });
        }

        $tours = $query->orderBy('base_price', 'asc')->paginate(9)->through(fn ($p) => [
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

        $tourTypes = collect(TourType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        return Inertia::render('Tours/Index', [
            'tours' => $tours,
            'tourTypes' => $tourTypes,
            'filters' => [
                'tour_type' => $tourType,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display a specific tour package detail.
     */
    public function show(string $slug): Response
    {
        $product = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::TOUR)
            ->where('slug', $slug)
            ->with([
                'tourPackage.itineraryDays.stops.destination',
                'tourPackage.departures',
                'partner',
                'media',
            ])
            ->firstOrFail();

        $tour = $product->tourPackage;

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
                'destination_slug' => $stop->destination?->slug,
            ]),
        ]);

        return Inertia::render('Tours/Show', [
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
                'tour' => [
                    'id' => $tour?->id,
                    'tour_type' => $tour?->tour_type?->label(),
                    'duration_days' => $tour?->duration_days,
                    'duration_nights' => $tour?->duration_nights,
                    'min_participants' => $tour?->min_participants,
                    'max_participants' => $tour?->max_participants,
                    'inclusions' => $tour?->inclusions,
                    'exclusions' => $tour?->exclusions,
                    'terms_conditions' => $tour?->terms_conditions,
                    'includes_ferry' => (bool) $tour?->includes_ferry,
                ],
                'itinerary' => $itinerary,
                'media' => $product->media->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->media_url,
                    'is_primary' => $m->is_primary,
                ]),
            ],
        ]);
    }
}


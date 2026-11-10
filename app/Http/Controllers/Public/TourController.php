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
            ->with(['tourPackage.itineraryDays.stops.destination.media', 'partner', 'media']);

        if ($tourType && in_array($tourType, array_column(TourType::cases(), 'value'))) {
            $query->whereHas('tourPackage', fn ($q) => $q->where('tour_type', $tourType));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('short_description', 'ilike', "%{$search}%");
            });
        }

        $tours = $query->orderBy('base_price', 'asc')->paginate(9)->through(function ($p) {
            $stops = $p->tourPackage?->itineraryDays?->flatMap(fn ($d) => $d->stops) ?? collect();
            $boundDestinations = $stops->map(fn ($s) => $s->destination?->name)->filter()->unique()->values();
            $firstDestMedia = $stops->map(fn ($s) => $s->destination?->media?->first()?->media_url)->filter()->first();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'short_description' => $p->short_description,
                'base_price' => (float) $p->base_price,
                'pricing_type' => $p->pricing_type->value,
                'dp_percentage' => (float) $p->dp_percentage,
                'min_lead_time_hours' => $p->min_lead_time_hours,
                'ownership_type' => $p->ownership_type->value,
                'partner_name' => $p->partner?->business_name ?? 'Platform Resmi Muter-Muter',
                'partner_rating' => (float) ($p->partner?->average_rating ?? 5.0),
                'partner_trips' => (int) ($p->partner?->completed_trips_count ?? 12),
                'duration_days' => $p->tourPackage?->duration_days ?? 1,
                'duration_nights' => $p->tourPackage?->duration_nights ?? 0,
                'tour_type' => $p->tourPackage?->tour_type?->label() ?? 'Tour',
                'includes_ferry' => (bool) $p->tourPackage?->includes_ferry,
                'bound_destinations' => $boundDestinations,
                'image_url' => $p->media->first()?->media_url
                    ?? $firstDestMedia
                    ?? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
            ];
        });

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
     * Display a specific tour package detail with Auto-Aggregated Destination Photos.
     */
    public function show(string $slug): Response
    {
        $product = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::TOUR)
            ->where('slug', $slug)
            ->with([
                'tourPackage.itineraryDays.stops.destination.media',
                'tourPackage.departures',
                'partner',
                'media',
            ])
            ->firstOrFail();

        $tour = $product->tourPackage;

        // 1. Build Enriched Itinerary with Destination Photo Binding
        $itinerary = $tour?->itineraryDays->map(fn ($day) => [
            'id' => $day->id,
            'day_number' => $day->day_number,
            'title' => $day->title,
            'description' => $day->description,
            'stops' => $day->stops->map(function ($stop) {
                $dest = $stop->destination;
                $primaryDestMedia = $dest?->media?->firstWhere('is_featured', true) ?? $dest?->media?->first();

                return [
                    'id' => $stop->id,
                    'stop_order' => $stop->stop_order,
                    'title' => $stop->title,
                    'activity_description' => $stop->activity_description,
                    'estimated_minutes' => $stop->estimated_minutes,
                    'destination_id' => $stop->destination_id,
                    'destination_name' => $dest?->name,
                    'destination_slug' => $dest?->slug,
                    'destination_cluster' => $dest?->cluster?->label(),
                    'destination_image_url' => $primaryDestMedia?->media_url,
                    'destination_ticket_price' => $dest ? (float) $dest->ticket_price_domestic : null,
                    'latitude' => $dest?->latitude !== null ? (float) $dest->latitude : null,
                    'longitude' => $dest?->longitude !== null ? (float) $dest->longitude : null,
                ];
            }),
        ]);

        // 2. Build Auto-Aggregated Gallery (Product Media + Bound Destination Media)
        $aggregatedMedia = collect();

        foreach ($product->media as $m) {
            $aggregatedMedia->push([
                'id' => 'prod-' . $m->id,
                'url' => $m->media_url,
                'caption' => $m->caption ?: $product->name,
                'location_name' => 'Sampul Paket Wisata',
                'location_slug' => null,
                'is_primary' => (bool) $m->is_primary,
            ]);
        }

        if ($tour && $tour->itineraryDays) {
            foreach ($tour->itineraryDays as $day) {
                foreach ($day->stops as $stop) {
                    $dest = $stop->destination;
                    if ($dest && $dest->media) {
                        foreach ($dest->media as $dm) {
                            // Prevent duplicate image URLs
                            if (!$aggregatedMedia->contains('url', $dm->media_url)) {
                                $aggregatedMedia->push([
                                    'id' => 'dest-' . $dm->id,
                                    'url' => $dm->media_url,
                                    'caption' => $dm->caption ?: $dest->name,
                                    'location_name' => $dest->name,
                                    'location_slug' => $dest->slug,
                                    'cluster_label' => $dest->cluster?->label(),
                                    'is_primary' => false,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        if ($aggregatedMedia->isEmpty()) {
            $aggregatedMedia->push([
                'id' => 'fallback-1',
                'url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                'caption' => $product->name,
                'location_name' => 'Kaldera Danau Toba',
                'location_slug' => null,
                'is_primary' => true,
            ]);
        }

        return Inertia::render('Tours/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'ownership_type' => $product->ownership_type->value,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'base_price' => (float) $product->base_price,
                'pricing_type' => $product->pricing_type->value,
                'dp_percentage' => (float) $product->dp_percentage,
                'min_lead_time_hours' => $product->min_lead_time_hours,
                'partner' => [
                    'id' => $product->partner?->id,
                    'name' => $product->partner?->business_name ?? 'Kurasi Resmi Platform Muter-Muter',
                    'city' => $product->partner?->city ?? 'Sumatera Utara',
                    'average_rating' => (float) ($product->partner?->average_rating ?? 4.9),
                    'completed_trips_count' => (int) ($product->partner?->completed_trips_count ?? 28),
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
                'media' => $aggregatedMedia->values(),
            ],
        ]);
    }

    /**
     * Display a printable, PDF-optimized official travel itinerary with Beta Tudia branding.
     */
    public function printItinerary(string $slug): Response
    {
        $product = Product::query()
            ->publishedAndApproved()
            ->where('product_type', ProductType::TOUR)
            ->where('slug', $slug)
            ->with([
                'tourPackage.itineraryDays.stops.destination.media',
                'partner',
                'media',
            ])
            ->firstOrFail();

        $tour = $product->tourPackage;

        $itinerary = $tour?->itineraryDays?->map(fn ($day) => [
            'id' => $day->id,
            'day_number' => $day->day_number,
            'title' => $day->title,
            'description' => $day->description,
            'stops' => $day->stops?->map(function ($stop) {
                $dest = $stop->destination;
                return [
                    'id' => $stop->id,
                    'stop_order' => $stop->stop_order,
                    'title' => $stop->activity_title,
                    'time_schedule' => $stop->time_schedule,
                    'description' => $stop->description,
                    'destination_name' => $dest?->name,
                    'destination_cluster' => $dest?->cluster?->label(),
                ];
            }),
        ]);

        return Inertia::render('Tours/ItineraryPrint', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'ownership_type' => $product->ownership_type->value,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'base_price' => (float) $product->base_price,
                'pricing_type' => $product->pricing_type->value,
                'dp_percentage' => (float) $product->dp_percentage,
                'partner' => [
                    'name' => $product->partner?->business_name ?? 'Kurasi Resmi Platform Muter-Muter',
                    'city' => $product->partner?->city ?? 'Sumatera Utara',
                ],
                'tour' => [
                    'id' => $tour?->id,
                    'tour_type' => $tour?->tour_type?->label(),
                    'duration_days' => $tour?->duration_days,
                    'duration_nights' => $tour?->duration_nights,
                    'inclusions' => $tour?->inclusions,
                    'exclusions' => $tour?->exclusions,
                    'terms_conditions' => $tour?->terms_conditions,
                    'includes_ferry' => (bool) $tour?->includes_ferry,
                ],
                'itinerary' => $itinerary,
            ],
        ]);
    }
}

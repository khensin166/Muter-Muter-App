<?php

namespace App\Http\Controllers\Public;

use App\Enums\DestinationCluster;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DestinationController extends Controller
{
    /**
     * Display a listing of destinations with filtering by cluster and category.
     */
    public function index(Request $request): Response
    {
        $selectedCluster = $request->query('cluster');
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $query = Destination::query()->with(['category', 'media']);

        if ($selectedCluster && in_array($selectedCluster, array_column(DestinationCluster::cases(), 'value'))) {
            $query->where('cluster', $selectedCluster);
        }

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $destinations = $query->orderBy('name', 'asc')->paginate(12)->through(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'slug' => $d->slug,
            'cluster' => $d->cluster->value,
            'cluster_label' => $d->cluster->label(),
            'category' => $d->category?->name,
            'description' => $d->description,
            'ticket_price_domestic' => (float) $d->ticket_price_domestic,
            'ticket_price_foreign' => (float) $d->ticket_price_foreign,
            'operating_hours' => $d->operating_hours,
            'image_url' => $d->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1578469550956-0e16b69c6a3d?auto=format&fit=crop&w=800&q=80',
        ]);

        $clusters = collect(DestinationCluster::cases())->map(fn ($c) => [
            'value' => $c->value,
            'label' => $c->label(),
        ]);

        $categories = DestinationCategory::all()->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
        ]);

        return Inertia::render('Destinations/Index', [
            'destinations' => $destinations,
            'clusters' => $clusters,
            'categories' => $categories,
            'filters' => [
                'cluster' => $selectedCluster,
                'category' => $selectedCategory,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display a specific destination detail.
     */
    public function show(string $slug): Response
    {
        $destination = Destination::query()
            ->with(['category', 'media', 'itineraryStops.day.tourPackage.product'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Nearby destinations in the same cluster
        $nearbyDestinations = Destination::query()
            ->with('media')
            ->where('cluster', $destination->cluster)
            ->where('id', '!=', $destination->id)
            ->take(3)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'slug' => $d->slug,
                'ticket_price_domestic' => (float) $d->ticket_price_domestic,
                'image_url' => $d->media->first()?->media_url ?? 'https://images.unsplash.com/photo-1578469550956-0e16b69c6a3d?auto=format&fit=crop&w=800&q=80',
            ]);

        // Related tour packages that visit this destination
        $relatedTours = $destination->itineraryStops
            ->map(fn ($stop) => $stop->day?->tourPackage?->product)
            ->filter(fn ($p) => $p && $p->isPublishedAndApproved())
            ->unique('id')
            ->take(2)
            ->values()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'base_price' => (float) $p->base_price,
                'duration_days' => $p->tourPackage?->duration_days ?? 1,
            ]);

        return Inertia::render('Destinations/Show', [
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'slug' => $destination->slug,
                'cluster' => $destination->cluster->value,
                'cluster_label' => $destination->cluster->label(),
                'category' => $destination->category?->name,
                'description' => $destination->description,
                'latitude' => (float) $destination->latitude,
                'longitude' => (float) $destination->longitude,
                'ticket_price_domestic' => (float) $destination->ticket_price_domestic,
                'ticket_price_foreign' => (float) $destination->ticket_price_foreign,
                'operating_hours' => $destination->operating_hours,
                'media' => $destination->media->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->media_url,
                    'is_primary' => $m->is_primary,
                ]),
            ],
            'nearbyDestinations' => $nearbyDestinations,
            'relatedTours' => $relatedTours,
        ]);
    }
}


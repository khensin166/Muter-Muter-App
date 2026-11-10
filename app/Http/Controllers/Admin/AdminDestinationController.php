<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationCategory;
use App\Models\DestinationCluster;
use App\Models\DestinationMedia;
use App\Services\FileSecurityService;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminDestinationController extends Controller
{
    /**
     * Display the Master Destinations directory in Admin Console.
     */
    public function index(Request $request): Response
    {
        $cluster = $request->query('cluster', '');

        $query = Destination::with(['category', 'media'])
            ->withCount('itineraryStops');

        if ($cluster) {
            $query->where('cluster', $cluster);
        }

        $destinations = $query->orderBy('cluster')->orderBy('name')->get()->map(function (Destination $dest) {
            $primaryMedia = $dest->media->firstWhere('is_primary', true) ?? $dest->media->first();

            return [
                'id' => $dest->id,
                'category_id' => $dest->category_id,
                'name' => $dest->name,
                'slug' => $dest->slug,
                'cluster' => $dest->cluster->value,
                'cluster_label' => $dest->cluster->label(),
                'category_name' => $dest->category?->name ?? 'Wisata Alam & Budaya',
                'description' => $dest->description,
                'short_description' => Str::limit(strip_tags((string) $dest->description), 140),
                'ticket_price_domestic' => (float) $dest->ticket_price_domestic,
                'ticket_price_foreign' => (float) $dest->ticket_price_foreign,
                'operating_hours' => $dest->operating_hours ?? '07:00 - 18:00 WIB',
                'latitude' => $dest->latitude ? (float) $dest->latitude : 2.6540,
                'longitude' => $dest->longitude ? (float) $dest->longitude : 98.8615,
                'is_featured' => (bool) $dest->is_featured,
                'is_active' => (bool) $dest->is_active,
                'media_count' => $dest->media->count(),
                'stops_usage_count' => $dest->itinerary_stops_count,
                'image_url' => $primaryMedia?->media_url ?? 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=800&q=80',
                'media_gallery' => $dest->media->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->media_url,
                    'file_path' => $m->file_path,
                    'caption' => $m->caption,
                    'is_primary' => (bool) $m->is_primary,
                ])->values(),
            ];
        });

        $clusters = DestinationCluster::activeOptions();

        $categories = DestinationCategory::orderBy('name')->get()->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
        ]);

        return Inertia::render('Admin/Destinations/Index', [
            'destinations' => $destinations,
            'clusters' => $clusters,
            'categories' => $categories,
            'currentCluster' => $cluster,
        ]);
    }

    /**
     * Store a newly created Master Destination with gallery photos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cluster' => ['required', 'string'],
            'category_id' => ['nullable', 'uuid', 'exists:destination_categories,id'],
            'description' => ['required', 'string'],
            'ticket_price_domestic' => ['nullable', 'numeric', 'min:0'],
            'ticket_price_foreign' => ['nullable', 'numeric', 'min:0'],
            'operating_hours' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $files = $request->file('photos');
            if (! is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $idx => $photo) {
                $photoPaths[] = '/storage/' . FileSecurityService::validateAndStore(
                    file: $photo,
                    directory: 'destinations',
                    fieldName: "photos.{$idx}",
                    allowPdf: false,
                    disk: 'public'
                );
            }
        }

        if (! empty($validated['image_urls'])) {
            foreach ($validated['image_urls'] as $url) {
                if (! empty(trim((string) $url))) {
                    $photoPaths[] = trim((string) $url);
                }
            }
        }

        $translator = app(TranslationService::class);
        $nameEn = $translator->translate($validated['name'], 'en', 'id');
        $descEn = $translator->translate($validated['description'], 'en', 'id');

        DB::transaction(function () use ($validated, $photoPaths, $nameEn, $descEn) {
            $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(4));

            $dest = Destination::create([
                'category_id' => $validated['category_id'] ?? DestinationCategory::first()?->id,
                'name' => ['id' => $validated['name'], 'en' => $nameEn],
                'slug' => $slug,
                'cluster' => $validated['cluster'],
                'description' => ['id' => $validated['description'], 'en' => $descEn],
                'latitude' => $validated['latitude'] ?? 2.6540,
                'longitude' => $validated['longitude'] ?? 98.8615,
                'operating_hours' => $validated['operating_hours'] ?? '07:00 - 18:00 WIB',
                'ticket_price_domestic' => $validated['ticket_price_domestic'] ?? 15000,
                'ticket_price_foreign' => $validated['ticket_price_foreign'] ?? 50000,
                'is_featured' => (bool) ($validated['is_featured'] ?? true),
                'is_active' => true,
            ]);

            if (empty($photoPaths)) {
                $photoPaths[] = 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1000&q=80';
            }

            foreach ($photoPaths as $index => $path) {
                DestinationMedia::create([
                    'destination_id' => $dest->id,
                    'file_path' => $path,
                    'caption' => $validated['name'] . ' — Galeri ' . ($index + 1),
                    'is_primary' => $index === 0,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi wisata berhasil ditambahkan ke katalog master.');
    }

    /**
     * Update an existing Master Destination and its media gallery.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $dest = Destination::with('media')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cluster' => ['required', 'string'],
            'category_id' => ['nullable', 'uuid', 'exists:destination_categories,id'],
            'description' => ['required', 'string'],
            'ticket_price_domestic' => ['nullable', 'numeric', 'min:0'],
            'ticket_price_foreign' => ['nullable', 'numeric', 'min:0'],
            'operating_hours' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'deleted_media_ids' => ['nullable', 'array'],
            'deleted_media_ids.*' => ['string'],
            'primary_media_id' => ['nullable', 'string'],
        ]);

        $translator = app(TranslationService::class);
        $nameEn = $translator->translate($validated['name'], 'en', 'id');
        $descEn = $translator->translate($validated['description'], 'en', 'id');

        DB::transaction(function () use ($dest, $validated, $nameEn, $descEn, $request) {
            $dest->update([
                'category_id' => $validated['category_id'] ?? $dest->category_id,
                'name' => ['id' => $validated['name'], 'en' => $nameEn],
                'cluster' => $validated['cluster'],
                'description' => ['id' => $validated['description'], 'en' => $descEn],
                'latitude' => $validated['latitude'] ?? $dest->latitude,
                'longitude' => $validated['longitude'] ?? $dest->longitude,
                'operating_hours' => $validated['operating_hours'] ?? $dest->operating_hours,
                'ticket_price_domestic' => $validated['ticket_price_domestic'] ?? $dest->ticket_price_domestic,
                'ticket_price_foreign' => $validated['ticket_price_foreign'] ?? $dest->ticket_price_foreign,
                'is_featured' => (bool) ($validated['is_featured'] ?? $dest->is_featured),
                'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $dest->is_active,
            ]);

            // Handle deleted media and clean up physical disk files
            if (! empty($validated['deleted_media_ids'])) {
                $mediaToDelete = DestinationMedia::where('destination_id', $dest->id)
                    ->whereIn('id', $validated['deleted_media_ids'])
                    ->get();

                foreach ($mediaToDelete as $mediaItem) {
                    $this->deletePhysicalMediaFile($mediaItem->file_path);
                    $mediaItem->delete();
                }
            }

            // Handle new file uploads (supports multiple files)
            $maxSortOrder = (int) DestinationMedia::where('destination_id', $dest->id)->max('sort_order');
            if ($request->hasFile('photos')) {
                $files = $request->file('photos');
                if (! is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $idx => $photo) {
                    $maxSortOrder++;
                    $storedPath = '/storage/' . FileSecurityService::validateAndStore(
                        file: $photo,
                        directory: 'destinations',
                        fieldName: "photos.{$idx}",
                        allowPdf: false,
                        disk: 'public'
                    );

                    DestinationMedia::create([
                        'destination_id' => $dest->id,
                        'file_path' => $storedPath,
                        'caption' => $validated['name'] . ' — Galeri ' . $maxSortOrder,
                        'is_primary' => false,
                        'sort_order' => $maxSortOrder,
                    ]);
                }
            }

            // Handle new image URLs
            if (! empty($validated['image_urls'])) {
                foreach ($validated['image_urls'] as $url) {
                    if (! empty(trim((string) $url))) {
                        $maxSortOrder++;
                        DestinationMedia::create([
                            'destination_id' => $dest->id,
                            'file_path' => trim((string) $url),
                            'caption' => $validated['name'] . ' — Galeri ' . $maxSortOrder,
                            'is_primary' => false,
                            'sort_order' => $maxSortOrder,
                        ]);
                    }
                }
            }

            // Update primary media
            if (! empty($validated['primary_media_id'])) {
                DestinationMedia::where('destination_id', $dest->id)->update(['is_primary' => false]);
                DestinationMedia::where('destination_id', $dest->id)
                    ->where('id', $validated['primary_media_id'])
                    ->update(['is_primary' => true]);
            } else {
                $hasPrimary = DestinationMedia::where('destination_id', $dest->id)->where('is_primary', true)->exists();
                if (! $hasPrimary) {
                    DestinationMedia::where('destination_id', $dest->id)->orderBy('sort_order')->first()?->update(['is_primary' => true]);
                }
            }
        });

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi wisata "' . $validated['name'] . '" berhasil diperbarui.');
    }

    /**
     * Delete an existing Master Destination and all its physical media files from disk.
     */
    public function destroy(string $id): RedirectResponse
    {
        $dest = Destination::withCount('itineraryStops')->with('media')->findOrFail($id);

        if ($dest->itinerary_stops_count > 0) {
            return back()->with('error', 'Destinasi "' . $dest->name . '" tidak dapat dihapus permanen karena sedang digunakan dalam ' . $dest->itinerary_stops_count . ' stop paket wisata aktif. Silakan nonaktifkan (toggle status) sebagai gantinya.');
        }

        DB::transaction(function () use ($dest) {
            foreach ($dest->media as $mediaItem) {
                $this->deletePhysicalMediaFile($mediaItem->file_path);
            }

            $dest->delete();
        });

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi wisata beserta berkas gambarnya berhasil dihapus dari katalog master.');
    }

    /**
     * Quick 1-click toggle active/inactive status for destination.
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $dest = Destination::findOrFail($id);
        $dest->is_active = ! $dest->is_active;
        $dest->save();

        $statusText = $dest->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Destinasi "' . $dest->name . '" berhasil ' . $statusText . '.');
    }

    /**
     * Safely delete physical media file from public disk if stored locally.
     */
    private function deletePhysicalMediaFile(?string $filePath): void
    {
        if (! $filePath || filter_var($filePath, FILTER_VALIDATE_URL)) {
            return;
        }

        $relative = preg_replace('#^/?(storage/)?#', '', trim($filePath));
        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}

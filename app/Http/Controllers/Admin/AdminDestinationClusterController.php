<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationCluster;
use App\Services\TranslationService;
use Database\Seeders\DestinationClusterSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminDestinationClusterController extends Controller
{
    /**
     * Display the Master Destination Clusters directory in Admin Console.
     */
    public function index(): Response
    {
        if (DestinationCluster::count() === 0) {
            app(DestinationClusterSeeder::class)->run();
        }

        $clusters = DestinationCluster::withCount('destinations')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->map(fn (DestinationCluster $cluster) => [
                'id' => $cluster->id,
                'code' => $cluster->code,
                'name' => $cluster->name,
                'description' => $cluster->description,
                'province' => $cluster->province ?? 'Sumatera Utara',
                'is_active' => (bool) $cluster->is_active,
                'sort_order' => (int) $cluster->sort_order,
                'destinations_count' => (int) $cluster->destinations_count,
                'updated_at' => $cluster->updated_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/DestinationClusters/Index', [
            'clusters' => $clusters,
        ]);
    }

    /**
     * Store a newly created Destination Cluster.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(preg_replace('/[^A-Za-z0-9_]/', '_', trim((string) $request->input('code')))),
        ]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:destination_clusters,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'province' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $translator = app(TranslationService::class);
        $nameEn = $translator->translate($validated['name'], 'en', 'id');
        $descEn = ! empty($validated['description'])
            ? $translator->translate($validated['description'], 'en', 'id')
            : null;

        DestinationCluster::create([
            'code' => $validated['code'],
            'name' => ['id' => $validated['name'], 'en' => $nameEn],
            'description' => ! empty($validated['description'])
                ? ['id' => $validated['description'], 'en' => $descEn]
                : null,
            'province' => $validated['province'] ?? 'Sumatera Utara',
            'sort_order' => $validated['sort_order'] ?? (DestinationCluster::max('sort_order') + 1),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.destination-clusters.index')
            ->with('success', 'Klaster wilayah "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Update an existing Destination Cluster.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $cluster = DestinationCluster::findOrFail($id);

        $request->merge([
            'code' => strtoupper(preg_replace('/[^A-Za-z0-9_]/', '_', trim((string) $request->input('code', $cluster->code)))),
        ]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('destination_clusters', 'code')->ignore($cluster->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'province' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $translator = app(TranslationService::class);
        $nameEn = $translator->translate($validated['name'], 'en', 'id');
        $descEn = ! empty($validated['description'])
            ? $translator->translate($validated['description'], 'en', 'id')
            : null;

        DB::transaction(function () use ($cluster, $validated, $nameEn, $descEn) {
            $oldCode = $cluster->code;

            $cluster->update([
                'code' => $validated['code'],
                'name' => ['id' => $validated['name'], 'en' => $nameEn],
                'description' => ! empty($validated['description'])
                    ? ['id' => $validated['description'], 'en' => $descEn]
                    : null,
                'province' => $validated['province'] ?? $cluster->province,
                'sort_order' => $validated['sort_order'] ?? $cluster->sort_order,
                'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $cluster->is_active,
            ]);

            if ($oldCode !== $validated['code']) {
                Destination::where('cluster', $oldCode)->update(['cluster' => $validated['code']]);
            }
        });

        return redirect()->route('admin.destination-clusters.index')
            ->with('success', 'Klaster wilayah "' . $validated['name'] . '" berhasil diperbarui.');
    }

    /**
     * Delete an existing Destination Cluster (blocked if destinations are bound).
     */
    public function destroy(string $id): RedirectResponse
    {
        $cluster = DestinationCluster::withCount('destinations')->findOrFail($id);

        if ($cluster->destinations_count > 0) {
            return back()->with('error', 'Klaster "' . $cluster->name . '" tidak dapat dihapus karena sedang digunakan oleh ' . $cluster->destinations_count . ' destinasi wisata. Silakan nonaktifkan status klaster sebagai gantinya.');
        }

        $cluster->delete();

        return redirect()->route('admin.destination-clusters.index')
            ->with('success', 'Klaster wilayah berhasil dihapus.');
    }

    /**
     * Quick 1-click toggle active/inactive status for a cluster.
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $cluster = DestinationCluster::findOrFail($id);
        $cluster->is_active = ! $cluster->is_active;
        $cluster->save();

        $statusText = $cluster->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Klaster "' . $cluster->name . '" berhasil ' . $statusText . '.');
    }
}


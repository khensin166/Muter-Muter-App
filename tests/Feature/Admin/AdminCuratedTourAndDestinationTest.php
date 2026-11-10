<?php

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\DestinationCluster;
use App\Enums\InventoryStatus;
use App\Enums\OwnershipType;
use App\Enums\PartnerStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\DestinationCategory;
use App\Models\DestinationMedia;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->category = DestinationCategory::create([
        'name' => ['id' => 'Wisata Alam Kaldera', 'en' => 'Caldera Nature'],
        'slug' => 'wisata-alam-kaldera',
        'icon' => 'mountain',
    ]);

    $this->destination = Destination::create([
        'category_id' => $this->category->id,
        'name' => ['id' => 'Bukit Holbung Samosir', 'en' => 'Holbung Hill Samosir'],
        'slug' => 'bukit-holbung-samosir',
        'cluster' => DestinationCluster::SAMOSIR_WEST,
        'short_description' => ['id' => 'Perbukitan hijau kaldera Toba.', 'en' => 'Green caldera hills of Lake Toba.'],
        'full_description' => ['id' => 'Spot panorama populer di Samosir Barat.', 'en' => 'Popular panoramic spot in West Samosir.'],
        'ticket_price_domestic' => 15000,
        'ticket_price_foreign' => 30000,
        'parking_car_fee' => 10000,
        'is_featured' => true,
        'is_active' => true,
    ]);

    DestinationMedia::create([
        'destination_id' => $this->destination->id,
        'file_path' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb',
        'caption' => 'Panorama Bukit Holbung',
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    $this->partner = Partner::create([
        'business_name' => 'Horas Samosir Transport',
        'brand_name' => 'Horas Transport',
        'pic_name' => 'Ucok Siregar',
        'pic_phone' => '081260001122',
        'email' => 'horas@transport.com',
        'address' => 'Jl. Ringroad Tuktuk',
        'city' => 'Samosir',
        'status' => PartnerStatus::APPROVED,
        'average_rating' => 4.95,
        'total_reviews_count' => 15,
        'completed_trips_count' => 34,
    ]);
});

test('super admin can create platform curated tour package with bound destination stops and auto-bind photos', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
        'name' => '6D5N Famtrip North Sumatra: KNO - Bukit Lawang - Danau Toba',
        'short_description' => 'Paket resmi platform lintas Sumatera Utara termasuk makan & rokok supir.',
        'full_description' => 'Rute lengkap KNO, Bukit Lawang, Medan, Parapat, dan Samosir dengan standar armada bersih.',
        'tour_type' => TourType::PRIVATE_TRIP->value,
        'base_price' => 7300000,
        'pricing_type' => PricingType::PER_GROUP->value,
        'dp_percentage' => 30,
        'duration_days' => 2,
        'duration_nights' => 1,
        'min_participants' => 2,
        'max_participants' => 6,
        'includes_ferry' => true,
        'inclusions' => '• Armada Bersih & AC Dingin\n• BBM PP\n• Makan & Rokok Supir',
        'itinerary' => [
            [
                'day_number' => 1,
                'title' => 'Hari 1: KNO - Parapat - Samosir',
                'description' => 'Perjalanan menuju Danau Toba',
                'stops' => [
                    [
                        'destination_id' => $this->destination->id,
                        'title' => 'Bukit Holbung Samosir',
                        'activity_description' => 'Menikmati panorama perbukitan kaldera.',
                        'estimated_minutes' => 120,
                    ],
                ],
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('ownership_type', OwnershipType::INTERNAL)->first();
    expect($product)->not->toBeNull();
    expect($product->status)->toBe(ProductStatus::PUBLISHED);
    expect($product->approval_status)->toBe(ApprovalStatus::APPROVED);
    expect($product->media)->toHaveCount(1);
    expect($product->media->first()->media_url)->toBe('https://images.unsplash.com/photo-1506744038136-46273834b3fb');

    // Public tour detail page should expose the bound destination photo and slug
    $showResponse = $this->get(route('tours.show', $product->slug));
    $showResponse->assertStatus(200);
});

test('super admin can dispatch booking to verified partner and assign driver', function () {
    $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

    $product = Product::create([
        'partner_id' => null,
        'ownership_type' => OwnershipType::INTERNAL,
        'product_type' => ProductType::TOUR,
        'name' => ['id' => 'Paket 1D Samosir', 'en' => '1D Samosir Package'],
        'slug' => 'paket-1d-samosir',
        'short_description' => ['id' => 'Keliling Samosir', 'en' => 'Around Samosir'],
        'full_description' => ['id' => 'Full dokumentasi', 'en' => 'Full documentation'],
        'base_price' => 1300000,
        'pricing_type' => PricingType::PER_GROUP,
        'dp_percentage' => 30,
        'min_lead_time_hours' => 24,
        'status' => ProductStatus::PUBLISHED,
        'approval_status' => ApprovalStatus::APPROVED,
    ]);

    $booking = Booking::create([
        'booking_code' => 'TB-20261110-DISP1',
        'user_id' => $customer->id,
        'product_id' => $product->id,
        'partner_id' => null,
        'booking_status' => BookingStatus::PENDING_APPROVAL,
        'payment_status' => PaymentStatus::UNPAID,
        'inventory_status' => InventoryStatus::NONE,
        'service_start_date' => now()->addDays(3)->toDateString(),
        'service_end_date' => now()->addDays(4)->toDateString(),
        'total_price' => 1300000,
        'dp_amount' => 390000,
        'total_paid' => 0,
        'commission_percentage' => 10,
        'platform_commission_amount' => 130000,
        'partner_payout_amount' => 1170000,
        'product_snapshot' => ['name' => 'Paket 1D Samosir'],
        'pricing_breakdown' => ['base_price' => 1300000],
        'cancellation_policy_snapshot' => ['gt_7d' => 100],
        'vendor_sla_deadline' => now()->addHours(24),
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.bookings.dispatch', $booking->id), [
        'partner_id' => $this->partner->id,
        'assigned_driver_name' => 'Bpk. Ucok Siregar',
        'assigned_driver_phone' => '081260001122',
        'assigned_vehicle_plate' => 'BK 1421 TZ',
    ]);

    $response->assertRedirect();

    $booking->refresh();
    expect($booking->partner_id)->toBe($this->partner->id);
    expect($booking->assigned_driver_name)->toBe('Bpk. Ucok Siregar');
    expect($booking->assigned_vehicle_plate)->toBe('BK 1421 TZ');
});

test('super admin can load admin operations dashboard with all metrics without query error', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->has('metrics')
        ->where('metrics.total_destinations', 1)
    );
});

test('public user can view and print official beta tudia itinerary document', function () {
    // Create an approved published tour
    $createResponse = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
        'name' => '6D5N Famtrip North Sumatra: KNO - Bukit Lawang - Danau Toba',
        'short_description' => 'Paket resmi platform lintas Sumatera Utara termasuk makan & rokok supir.',
        'full_description' => 'Rute lengkap KNO, Bukit Lawang, Medan, Parapat, dan Samosir.',
        'base_price' => 1200000,
        'pricing_type' => PricingType::PER_PERSON->value,
        'dp_percentage' => 30,
        'tour_type' => TourType::PRIVATE_TRIP->value,
        'duration_days' => 6,
        'duration_nights' => 5,
        'min_participants' => 2,
        'max_participants' => 12,
        'inclusions' => "• Armada Bersih & AC Dingin\n• Makan & Rokok Supir",
        'exclusions' => 'Tiket pesawat',
        'terms_conditions' => 'Ketentuan resmi',
        'includes_ferry' => true,
        'itinerary' => [
            [
                'day_number' => 1,
                'title' => 'Hari 1: KNO - Bukit Lawang',
                'description' => 'Kedatangan bandara dan perjalanan ke Bukit Lawang',
                'stops' => [
                    [
                        'stop_order' => 1,
                        'title' => 'Kedatangan di Bandara KNO',
                        'activity_description' => 'Penyambutan dan perjalanan',
                        'estimated_minutes' => 180,
                        'destination_id' => $this->destination->id,
                    ],
                ],
            ],
        ],
    ]);

    $createResponse->assertSessionHasNoErrors();
    $createResponse->assertRedirect();
    $product = Product::where('product_type', ProductType::TOUR)->latest()->first();

    $printResponse = $this->get(route('tours.itinerary.print', $product->slug));
    $printResponse->assertOk();
    $printResponse->assertInertia(fn ($page) => $page
        ->component('Tours/ItineraryPrint')
        ->has('product')
        ->where('product.tour.duration_days', 6)
        ->where('product.tour.duration_nights', 5)
    );
});

test('admin can store, update, toggle and delete destination in master catalog', function () {
    // 1. Store
    $storeResponse = $this->actingAs($this->admin)->post(route('admin.destinations.store'), [
        'name' => 'Air Terjun Sipiso Piso',
        'cluster' => DestinationCluster::MEREK_TONGGING->value,
        'category_id' => $this->category->id,
        'description' => 'Air terjun megah setinggi 120 meter di bibir utara Danau Toba.',
        'ticket_price_domestic' => 10000,
        'ticket_price_foreign' => 25000,
        'operating_hours' => '07:00 - 18:00 WIB',
        'latitude' => 2.9182,
        'longitude' => 98.5284,
        'is_featured' => true,
        'image_urls' => ['https://images.unsplash.com/photo-sipiso-piso'],
    ]);

    $storeResponse->assertRedirect(route('admin.destinations.index'));
    $dest = Destination::where('slug', 'like', 'air-terjun-sipiso-piso%')->first();
    expect($dest)->not->toBeNull();
    expect($dest->media()->count())->toBe(1);

    // 2. Toggle Status
    $toggleResponse = $this->actingAs($this->admin)->patch(route('admin.destinations.toggle', $dest->id));
    $toggleResponse->assertRedirect();
    expect($dest->fresh()->is_active)->toBeFalse();

    // 3. Update
    $updateResponse = $this->actingAs($this->admin)->post(route('admin.destinations.update', $dest->id), [
        'name' => 'Air Terjun Sipiso Piso Merek',
        'cluster' => DestinationCluster::MEREK_TONGGING->value,
        'category_id' => $this->category->id,
        'description' => 'Air terjun megah tertinggi di Sumatera Utara.',
        'ticket_price_domestic' => 12000,
        'ticket_price_foreign' => 30000,
        'operating_hours' => '06:30 - 18:00 WIB',
        'is_active' => true,
    ]);
    $updateResponse->assertRedirect(route('admin.destinations.index'));
    expect($dest->fresh()->name)->toBe('Air Terjun Sipiso Piso Merek');
    expect($dest->fresh()->ticket_price_domestic)->toEqual('12000.00');

    // 4. Delete un-bound destination
    $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.destinations.destroy', $dest->id));
    $deleteResponse->assertRedirect(route('admin.destinations.index'));
    expect(Destination::find($dest->id))->toBeNull();
});

test('admin cannot hard delete destination if bound to tour itinerary stops', function () {
    // Bind $this->destination to a tour itinerary stop
    $this->actingAs($this->admin)->post(route('admin.tours.store'), [
        'name' => 'Caldera Special 1 Day',
        'short_description' => 'Tur singkat 1 hari Danau Toba.',
        'full_description' => 'Tur singkat 1 hari Danau Toba deskripsi lengkap.',
        'base_price' => 500000,
        'pricing_type' => PricingType::PER_PERSON->value,
        'dp_percentage' => 30,
        'tour_type' => TourType::OPEN_TRIP->value,
        'duration_days' => 1,
        'duration_nights' => 0,
        'min_participants' => 1,
        'max_participants' => 10,
        'inclusions' => 'Transportasi',
        'exclusions' => 'Pengeluaran pribadi',
        'terms_conditions' => 'Ketentuan tur',
        'includes_ferry' => false,
        'itinerary' => [
            [
                'day_number' => 1,
                'title' => 'Hari 1',
                'description' => 'Deskripsi hari 1',
                'stops' => [
                    [
                        'stop_order' => 1,
                        'title' => 'Kunjungan Bukit Holbung',
                        'activity_description' => 'Tracking ringan',
                        'estimated_minutes' => 90,
                        'destination_id' => $this->destination->id,
                    ],
                ],
            ],
        ],
    ]);

    // Attempt delete
    $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.destinations.destroy', $this->destination->id));
    $deleteResponse->assertSessionHas('error');
    expect(Destination::find($this->destination->id))->not->toBeNull();
});

test('customer can access dashboard without payment status constant error', function () {
    $customer = User::factory()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $response = $this->actingAs($customer)->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('metrics')
        ->has('userProfile')
    );
});

test('admin can manage dynamic destination clusters via full CRUD', function () {
    // 1. Index auto-seeds baseline clusters
    $indexResponse = $this->actingAs($this->admin)->get(route('admin.destination-clusters.index'));
    $indexResponse->assertOk();

    // 2. Store new dynamic cluster
    $storeResponse = $this->actingAs($this->admin)->post(route('admin.destination-clusters.store'), [
        'code' => 'nias_selatan',
        'name' => 'Nias Selatan & Teluk Lagundri',
        'description' => 'Kawasan wisata selancar kelas dunia dan lompat batu Bawomataluo.',
        'sort_order' => 10,
        'is_active' => true,
    ]);
    $storeResponse->assertRedirect(route('admin.destination-clusters.index'));

    $cluster = \App\Models\DestinationCluster::where('code', 'NIAS_SELATAN')->first();
    expect($cluster)->not->toBeNull();
    expect($cluster->name)->toBe('Nias Selatan & Teluk Lagundri');

    // 3. Create a destination using the new dynamic cluster
    $dest = Destination::create([
        'category_id' => $this->category->id,
        'name' => ['id' => 'Desa Adat Bawomataluo', 'en' => 'Bawomataluo Traditional Village'],
        'slug' => 'desa-adat-bawomataluo',
        'cluster' => 'NIAS_SELATAN',
        'description' => ['id' => 'Desa lompat batu Nias.', 'en' => 'Stone jumping village in Nias.'],
        'ticket_price_domestic' => 15000,
        'ticket_price_foreign' => 30000,
        'is_featured' => true,
        'is_active' => true,
    ]);
    expect($dest->cluster->value)->toBe('NIAS_SELATAN');
    expect($dest->cluster->label())->toBe('Nias Selatan & Teluk Lagundri');

    // 4. Cannot delete cluster while destination is bound to it
    $blockedDelete = $this->actingAs($this->admin)->delete(route('admin.destination-clusters.destroy', $cluster->id));
    $blockedDelete->assertSessionHas('error');
    expect(\App\Models\DestinationCluster::find($cluster->id))->not->toBeNull();

    // 5. Update cluster code cascades to bound destination
    $updateResponse = $this->actingAs($this->admin)->post(route('admin.destination-clusters.update', $cluster->id), [
        'code' => 'PULAU_NIAS',
        'name' => 'Kepulauan Nias & Lagundri',
        'description' => 'Deskripsi terbaru Nias.',
        'sort_order' => 11,
        'is_active' => true,
    ]);
    $updateResponse->assertRedirect(route('admin.destination-clusters.index'));
    expect($dest->fresh()->cluster->value)->toBe('PULAU_NIAS');

    // 6. Remove destination and delete cluster cleanly
    $dest->delete();
    $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.destination-clusters.destroy', $cluster->id));
    $deleteResponse->assertRedirect(route('admin.destination-clusters.index'));
    expect(\App\Models\DestinationCluster::find($cluster->id))->toBeNull();
});

test('deleting a destination removes its physical media files from public storage disk', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('destinations/test_image_cleanup.jpg', 'fake-image-bytes');

    $dest = Destination::create([
        'category_id' => $this->category->id,
        'name' => ['id' => 'Pantai Pasir Putih Parbaba', 'en' => 'Parbaba White Sand Beach'],
        'slug' => 'pantai-pasir-putih-parbaba',
        'cluster' => DestinationCluster::SAMOSIR_WEST,
        'description' => ['id' => 'Pantai pasir putih danau.', 'en' => 'Lake white sand beach.'],
        'ticket_price_domestic' => 10000,
        'ticket_price_foreign' => 20000,
        'is_featured' => false,
        'is_active' => true,
    ]);

    DestinationMedia::create([
        'destination_id' => $dest->id,
        'file_path' => '/storage/destinations/test_image_cleanup.jpg',
        'caption' => 'Foto Parbaba',
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    expect(\Illuminate\Support\Facades\Storage::disk('public')->exists('destinations/test_image_cleanup.jpg'))->toBeTrue();

    $this->actingAs($this->admin)->delete(route('admin.destinations.destroy', $dest->id))
        ->assertRedirect(route('admin.destinations.index'));

    expect(\Illuminate\Support\Facades\Storage::disk('public')->exists('destinations/test_image_cleanup.jpg'))->toBeFalse();
});

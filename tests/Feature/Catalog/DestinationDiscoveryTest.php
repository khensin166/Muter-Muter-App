<?php

namespace Tests\Feature\Catalog;

use App\Enums\DestinationCluster;
use App\Models\Destination;
use App\Models\DestinationCategory;

beforeEach(function () {
    $category = DestinationCategory::create([
        'name' => 'Wisata Alam',
        'slug' => 'wisata-alam',
    ]);

    $this->destParapat = Destination::create([
        'category_id' => $category->id,
        'name' => 'Pantai Bebas Parapat',
        'slug' => 'pantai-bebas-parapat',
        'cluster' => DestinationCluster::PARAPAT,
        'description' => 'Taman rekreasi tepi danau di Parapat.',
        'ticket_price_domestic' => 0,
        'ticket_price_foreign' => 0,
        'operating_hours' => '24 Jam',
        'is_featured' => true,
    ]);

    $this->destSamosir = Destination::create([
        'category_id' => $category->id,
        'name' => 'Bukit Holbung Samosir',
        'slug' => 'bukit-holbung-samosir',
        'cluster' => DestinationCluster::SAMOSIR_WEST,
        'description' => 'Bukit teletubbies dengan pemandangan kaldera.',
        'ticket_price_domestic' => 10000,
        'ticket_price_foreign' => 20000,
        'operating_hours' => '06:00 - 18:00',
        'is_featured' => true,
    ]);
});

test('public can view destination listing', function () {
    $response = $this->get(route('destinations.index'));

    $response->assertStatus(200);
});

test('public can filter destinations by cluster', function () {
    $response = $this->get(route('destinations.index', ['cluster' => DestinationCluster::PARAPAT->value]));

    $response->assertStatus(200);
});

test('public can view single destination detail', function () {
    $response = $this->get(route('destinations.show', $this->destParapat->slug));

    $response->assertStatus(200);
});


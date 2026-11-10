<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add missing performance, relational, and concurrency indexes.
     */
    public function up(): void
    {
        // 1. Partners: Speed up partner dispatch selector and ranking queries
        Schema::table('partners', function (Blueprint $table) {
            $table->index(['status', 'average_rating'], 'idx_partners_status_rating');
            $table->index('completed_trips_count', 'idx_partners_completed_trips');
        });

        // 2. Tour Itinerary Stops: Speed up destination relationship and stop counts
        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            $table->index('destination_id', 'idx_tour_stops_destination_id');
            $table->index(['tour_itinerary_day_id', 'stop_order'], 'idx_tour_stops_day_order');
        });

        // 3. Destination Media: Speed up primary photo lookups and gallery ordering
        Schema::table('destination_media', function (Blueprint $table) {
            $table->index(['destination_id', 'is_primary', 'sort_order'], 'idx_dest_media_lookup');
        });

        // 4. Destinations: Speed up cluster and featured destination queries
        Schema::table('destinations', function (Blueprint $table) {
            $table->index(['cluster', 'is_featured'], 'idx_destinations_cluster_featured');
        });

        // 5. Products: Speed up internal vs partner queries
        Schema::table('products', function (Blueprint $table) {
            $table->index(['ownership_type', 'status', 'approval_status'], 'idx_products_ownership_status');
        });

        // 6. Bookings: Speed up cron jobs (trip completion, SLA check, H-1 contact unmasking)
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['booking_status', 'service_end_date'], 'idx_bookings_status_end_date');
            $table->index(['booking_status', 'service_start_date', 'contact_unmasked'], 'idx_bookings_unmask_dispatch');
        });

        // 7. Inventory Holds: Speed up vehicle & departure concurrency checks
        Schema::table('inventory_holds', function (Blueprint $table) {
            $table->index(['vehicle_id', 'status', 'expires_at'], 'idx_holds_vehicle_concurrency');
            $table->index(['tour_departure_id', 'status', 'expires_at'], 'idx_holds_departure_concurrency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_holds', function (Blueprint $table) {
            $table->dropIndex('idx_holds_departure_concurrency');
            $table->dropIndex('idx_holds_vehicle_concurrency');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_unmask_dispatch');
            $table->dropIndex('idx_bookings_status_end_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_ownership_status');
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex('idx_destinations_cluster_featured');
        });

        Schema::table('destination_media', function (Blueprint $table) {
            $table->dropIndex('idx_dest_media_lookup');
        });

        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            $table->dropIndex('idx_tour_stops_day_order');
            $table->dropIndex('idx_tour_stops_destination_id');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropIndex('idx_partners_completed_trips');
            $table->dropIndex('idx_partners_status_rating');
        });
    }
};

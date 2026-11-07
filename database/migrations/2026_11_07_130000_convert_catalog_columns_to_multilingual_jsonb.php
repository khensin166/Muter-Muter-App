<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // 1. Destination Categories
            DB::statement("ALTER TABLE destination_categories ALTER COLUMN name TYPE jsonb USING jsonb_build_object('id', name)");

            // 2. Destinations
            DB::statement("ALTER TABLE destinations ALTER COLUMN name TYPE jsonb USING jsonb_build_object('id', name)");
            DB::statement("ALTER TABLE destinations ALTER COLUMN description TYPE jsonb USING jsonb_build_object('id', description)");

            // 3. Products
            DB::statement("ALTER TABLE products ALTER COLUMN name TYPE jsonb USING jsonb_build_object('id', name)");
            DB::statement("ALTER TABLE products ALTER COLUMN short_description TYPE jsonb USING jsonb_build_object('id', short_description)");
            DB::statement("ALTER TABLE products ALTER COLUMN full_description TYPE jsonb USING jsonb_build_object('id', full_description)");

            // 4. Tour Packages
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN inclusions TYPE jsonb USING jsonb_build_object('id', inclusions)");
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN exclusions TYPE jsonb USING jsonb_build_object('id', exclusions)");
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN terms_conditions TYPE jsonb USING jsonb_build_object('id', terms_conditions)");

            // 5. Itinerary Days & Stops
            DB::statement("ALTER TABLE tour_itinerary_days ALTER COLUMN title TYPE jsonb USING jsonb_build_object('id', title)");
            DB::statement("ALTER TABLE tour_itinerary_days ALTER COLUMN description TYPE jsonb USING jsonb_build_object('id', description)");

            DB::statement("ALTER TABLE tour_itinerary_stops ALTER COLUMN title TYPE jsonb USING jsonb_build_object('id', title)");
            DB::statement("ALTER TABLE tour_itinerary_stops ALTER COLUMN activity_description TYPE jsonb USING jsonb_build_object('id', activity_description)");

            // 6. PostgreSQL Indexes (B-Tree Expression & GIN Full-Text Search)
            DB::statement("CREATE INDEX IF NOT EXISTS idx_products_name_id ON products ((name->>'id'))");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_products_name_en ON products ((name->>'en'))");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_destinations_name_id ON destinations ((name->>'id'))");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_destinations_name_en ON destinations ((name->>'en'))");

            // Full-text search indexes
            DB::statement("CREATE INDEX IF NOT EXISTS idx_products_search_id ON products USING gin(to_tsvector('indonesian', coalesce(name->>'id', '') || ' ' || coalesce(short_description->>'id', '')))");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_products_search_en ON products USING gin(to_tsvector('english', coalesce(name->>'en', '') || ' ' || coalesce(short_description->>'en', '')))");
        } else {
            // SQLite / Fallback for in-memory testing
            Schema::table('destination_categories', function (Blueprint $table) {
                $table->json('name')->change();
            });
            Schema::table('destinations', function (Blueprint $table) {
                $table->json('name')->change();
                $table->json('description')->nullable()->change();
            });
            Schema::table('products', function (Blueprint $table) {
                $table->json('name')->change();
                $table->json('short_description')->nullable()->change();
                $table->json('full_description')->nullable()->change();
            });
            Schema::table('tour_packages', function (Blueprint $table) {
                $table->json('inclusions')->nullable()->change();
                $table->json('exclusions')->nullable()->change();
                $table->json('terms_conditions')->nullable()->change();
            });
            Schema::table('tour_itinerary_days', function (Blueprint $table) {
                $table->json('title')->change();
                $table->json('description')->nullable()->change();
            });
            Schema::table('tour_itinerary_stops', function (Blueprint $table) {
                $table->json('title')->change();
                $table->json('activity_description')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS idx_products_search_en");
            DB::statement("DROP INDEX IF EXISTS idx_products_search_id");
            DB::statement("DROP INDEX IF EXISTS idx_destinations_name_en");
            DB::statement("DROP INDEX IF EXISTS idx_destinations_name_id");
            DB::statement("DROP INDEX IF EXISTS idx_products_name_en");
            DB::statement("DROP INDEX IF EXISTS idx_products_name_id");

            DB::statement("ALTER TABLE destination_categories ALTER COLUMN name TYPE varchar USING (name->>'id')");
            DB::statement("ALTER TABLE destinations ALTER COLUMN name TYPE varchar USING (name->>'id')");
            DB::statement("ALTER TABLE destinations ALTER COLUMN description TYPE text USING (description->>'id')");
            DB::statement("ALTER TABLE products ALTER COLUMN name TYPE varchar USING (name->>'id')");
            DB::statement("ALTER TABLE products ALTER COLUMN short_description TYPE text USING (short_description->>'id')");
            DB::statement("ALTER TABLE products ALTER COLUMN full_description TYPE text USING (full_description->>'id')");
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN inclusions TYPE text USING (inclusions->>'id')");
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN exclusions TYPE text USING (exclusions->>'id')");
            DB::statement("ALTER TABLE tour_packages ALTER COLUMN terms_conditions TYPE text USING (terms_conditions->>'id')");
            DB::statement("ALTER TABLE tour_itinerary_days ALTER COLUMN title TYPE varchar USING (title->>'id')");
            DB::statement("ALTER TABLE tour_itinerary_days ALTER COLUMN description TYPE text USING (description->>'id')");
            DB::statement("ALTER TABLE tour_itinerary_stops ALTER COLUMN title TYPE varchar USING (title->>'id')");
            DB::statement("ALTER TABLE tour_itinerary_stops ALTER COLUMN activity_description TYPE text USING (activity_description->>'id')");
        }
    }
};

<?php

use App\Models\Destination;
use App\Models\Partner;
use Database\Seeders\DestinationSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Backfill bilingual (ID & EN) translations for existing seeded catalog records.
     */
    public function up(): void
    {
        if (Destination::count() > 0) {
            app(DestinationSeeder::class)->run();
        }

        if (Partner::count() > 0 && Destination::count() > 0) {
            app(ProductSeeder::class)->run();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive translation backfill
    }
};

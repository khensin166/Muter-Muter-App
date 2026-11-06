<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_itinerary_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->integer('day_number')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tour_package_id', 'day_number']);
        });

        Schema::create('tour_itinerary_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tour_itinerary_day_id')->constrained('tour_itinerary_days')->cascadeOnDelete();
            $table->foreignUuid('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->integer('stop_order')->default(1);
            $table->string('title');
            $table->text('activity_description')->nullable();
            $table->integer('estimated_minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('tour_departures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->date('departure_date');
            $table->time('departure_time')->nullable();
            $table->integer('max_quota')->default(20);
            $table->integer('booked_quota')->default(0);
            $table->decimal('price_override', 14, 2)->nullable();
            $table->string('status', 50)->default('OPEN'); // OPEN, CLOSED, CANCELLED
            $table->timestamps();

            $table->index(['tour_package_id', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_departures');
        Schema::dropIfExists('tour_itinerary_stops');
        Schema::dropIfExists('tour_itinerary_days');
    }
};


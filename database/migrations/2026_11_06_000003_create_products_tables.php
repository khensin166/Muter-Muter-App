<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('ownership_type', 50)->default('PARTNER'); // INTERNAL, PARTNER
            $table->string('product_type', 50); // TOUR, VEHICLE, TRANSFER
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->decimal('base_price', 14, 2);
            $table->string('pricing_type', 50)->default('PER_PERSON');
            $table->decimal('dp_percentage', 5, 2)->default(30.00);
            $table->integer('min_lead_time_hours')->default(24);
            $table->string('status', 50)->default('DRAFT'); // DRAFT, PUBLISHED, ARCHIVED, SUSPENDED
            $table->string('approval_status', 50)->default('DRAFT'); // DRAFT, PENDING_REVIEW, APPROVED, REJECTED
            $table->timestamps();

            $table->index(['product_type', 'status', 'approval_status']);
            $table->index('partner_id');
        });

        Schema::create('tour_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('tour_type', 50)->default('PRIVATE_TRIP');
            $table->integer('duration_days')->default(1);
            $table->integer('duration_nights')->default(0);
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->default(20);
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->boolean('includes_ferry')->default(false);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('vehicle_type', 50)->default('CAR'); // CAR, MINIBUS, BUS, BOAT
            $table->string('brand');
            $table->string('model');
            $table->integer('manufacturing_year')->nullable();
            $table->integer('passenger_capacity')->default(4);
            $table->integer('luggage_capacity')->default(2);
            $table->string('transmission', 20)->default('MANUAL'); // MANUAL, AUTOMATIC
            $table->boolean('with_driver')->default(true);
            $table->string('license_plate', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_approval_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('previous_status', 50);
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_approval_logs');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('tour_packages');
        Schema::dropIfExists('products');
    }
};


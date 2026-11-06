<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('booking_code', 50)->unique();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            
            // Explicit Independent State Machines
            $table->string('booking_status', 50)->default('PENDING_APPROVAL');
            $table->string('payment_status', 50)->default('UNPAID');
            $table->string('inventory_status', 50)->default('NONE');

            $table->date('service_start_date');
            $table->date('service_end_date');
            $table->time('service_start_time')->nullable();

            // Financial Snapshot
            $table->decimal('total_price', 14, 2);
            $table->decimal('dp_amount', 14, 2);
            $table->decimal('total_paid', 14, 2)->default(0.00);
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->decimal('platform_commission_amount', 14, 2)->default(0.00);
            $table->decimal('partner_payout_amount', 14, 2)->default(0.00);

            // Immutability Snapshots (JSONB)
            $table->jsonb('product_snapshot');
            $table->jsonb('pricing_breakdown');
            $table->jsonb('cancellation_policy_snapshot');

            // 24-Hour Vendor SLA Deadline
            $table->timestampTz('vendor_sla_deadline');
            $table->timestamps();

            $table->index(['booking_status', 'payment_status', 'inventory_status']);
            $table->index('vendor_sla_deadline');
            $table->index('partner_id');
            $table->index('user_id');
        });

        Schema::create('booking_tour_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('tour_departure_id')->nullable()->constrained('tour_departures')->nullOnDelete();
            $table->integer('participant_count')->default(1);
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_vehicle_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('pickup_location');
            $table->string('dropoff_location')->nullable();
            $table->time('pickup_time')->nullable();
            $table->text('driver_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 50)->default('SYSTEM');
            $table->string('previous_status', 50);
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_histories');
        Schema::dropIfExists('booking_vehicle_details');
        Schema::dropIfExists('booking_tour_details');
        Schema::dropIfExists('bookings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('tour_departure_id')->nullable()->constrained('tour_departures')->nullOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->integer('held_quantity')->default(1);
            $table->timestampTz('held_at');
            $table->timestampTz('expires_at');
            $table->string('status', 50)->default('ACTIVE'); // ACTIVE, RELEASED, CONVERTED
            $table->timestamps();

            $table->index(['expires_at', 'status']);
            $table->index('booking_id');
        });

        Schema::create('vehicle_unavailabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->timestampTz('start_datetime');
            $table->timestampTz('end_datetime');
            $table->string('reason', 100)->default('RENTED');
            $table->timestamps();

            $table->index(['vehicle_id', 'start_datetime', 'end_datetime']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('payment_code', 50)->unique();
            $table->string('payment_type', 50); // DOWN_PAYMENT, BALANCE_SETTLEMENT, FULL_PAYMENT
            $table->string('payment_method', 50)->default('MANUAL_BANK_TRANSFER');
            $table->decimal('amount', 14, 2);
            $table->string('status', 50)->default('AWAITING_PAYMENT'); // AWAITING_PAYMENT, PAYMENT_REVIEW, VERIFIED, REJECTED
            $table->timestampTz('paid_at')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('status');
        });

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('bank_sender_name', 100)->nullable();
            $table->string('bank_sender_account', 100)->nullable();
            $table->string('bank_destination_name', 100)->nullable();
            $table->timestampTz('transferred_at')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->text('verification_note')->nullable();
            $table->timestamps();

            $table->index('payment_id');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->decimal('calculated_refund_percentage', 5, 2);
            $table->decimal('refund_amount', 14, 2);
            $table->text('reason')->nullable();
            $table->string('bank_name', 100);
            $table->string('bank_account_number', 100);
            $table->string('bank_account_holder', 255);
            $table->string('status', 50)->default('PENDING'); // PENDING, APPROVED, PROCESSED, REJECTED
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('vendor_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->decimal('gross_amount', 14, 2);
            $table->decimal('commission_amount', 14, 2);
            $table->decimal('net_payout_amount', 14, 2);
            $table->string('status', 50)->default('PENDING'); // PENDING, ELIGIBLE, PROCESSING, PAID, ON_HOLD
            $table->string('bank_reference', 100)->nullable();
            $table->timestampTz('eligible_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'status']);
            $table->index('booking_id');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 100);
            $table->uuid('entity_id');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('vendor_settlements');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('vehicle_unavailabilities');
        Schema::dropIfExists('inventory_holds');
    }
};

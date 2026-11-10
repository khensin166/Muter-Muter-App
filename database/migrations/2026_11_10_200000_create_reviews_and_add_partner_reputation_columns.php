<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(5.00)->after('status');
            }
            if (!Schema::hasColumn('partners', 'total_reviews_count')) {
                $table->integer('total_reviews_count')->default(0)->after('average_rating');
            }
            if (!Schema::hasColumn('partners', 'completed_trips_count')) {
                $table->integer('completed_trips_count')->default(0)->after('total_reviews_count');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'contact_unmasked')) {
                $table->boolean('contact_unmasked')->default(false)->after('vendor_sla_deadline');
            }
            if (!Schema::hasColumn('bookings', 'assigned_driver_name')) {
                $table->string('assigned_driver_name', 150)->nullable()->after('contact_unmasked');
            }
            if (!Schema::hasColumn('bookings', 'assigned_driver_phone')) {
                $table->string('assigned_driver_phone', 50)->nullable()->after('assigned_driver_name');
            }
            if (!Schema::hasColumn('bookings', 'assigned_vehicle_plate')) {
                $table->string('assigned_vehicle_plate', 50)->nullable()->after('assigned_driver_phone');
            }
        });

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('partner_id')->nullable()->constrained('partners')->nullOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->tinyInteger('rating')->default(5);
                $table->tinyInteger('driver_rating')->nullable();
                $table->tinyInteger('vehicle_rating')->nullable();
                $table->text('comment')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();

                $table->index(['partner_id', 'is_published']);
                $table->index(['product_id', 'is_published']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_unmasked',
                'assigned_driver_name',
                'assigned_driver_phone',
                'assigned_vehicle_plate',
            ]);
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'average_rating',
                'total_reviews_count',
                'completed_trips_count',
            ]);
        });
    }
};


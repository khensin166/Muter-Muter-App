<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'original_partner_id')) {
                $table->foreignUuid('original_partner_id')->nullable()->after('partner_id')->constrained('partners')->nullOnDelete();
                $table->index('original_partner_id');
            }
            if (!Schema::hasColumn('bookings', 'assigned_at')) {
                $table->timestampTz('assigned_at')->nullable()->after('vendor_sla_deadline');
                $table->index('assigned_at');
            }
            if (!Schema::hasColumn('bookings', 'assigned_by_id')) {
                $table->foreignUuid('assigned_by_id')->nullable()->after('assigned_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('assigned_vehicle_plate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'original_partner_id')) {
                $table->dropIndex(['original_partner_id']);
                $table->dropConstrainedForeignId('original_partner_id');
            }
            if (Schema::hasColumn('bookings', 'assigned_by_id')) {
                $table->dropConstrainedForeignId('assigned_by_id');
            }
            if (Schema::hasColumn('bookings', 'assigned_at')) {
                $table->dropIndex(['assigned_at']);
                $table->dropColumn('assigned_at');
            }
            if (Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};

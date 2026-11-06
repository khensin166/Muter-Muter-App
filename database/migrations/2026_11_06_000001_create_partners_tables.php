<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_name');
            $table->string('brand_name')->nullable();
            $table->string('pic_name');
            $table->string('pic_phone', 30);
            $table->string('email')->unique();
            $table->text('address')->nullable();
            $table->string('city', 100)->default('Samosir');
            $table->string('status', 50)->default('PENDING');
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_holder', 255)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('partner_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 50)->default('STAFF'); // OWNER or STAFF
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['partner_id', 'user_id']);
        });

        Schema::create('partner_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('document_type', 50); // KTP, NIB, FLEET_PHOTO
            $table->string('file_path');
            $table->string('verification_status', 50)->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_documents');
        Schema::dropIfExists('partner_members');
        Schema::dropIfExists('partners');
    }
};

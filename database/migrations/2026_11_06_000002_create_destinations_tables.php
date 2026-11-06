<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('destination_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('cluster', 50); // PARAPAT, SAMOSIR_EAST, SAMOSIR_WEST, MEREK_TONGGING, MEDAN_HUB
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('ticket_price_domestic', 14, 2)->default(0);
            $table->decimal('ticket_price_foreign', 14, 2)->default(0);
            $table->string('operating_hours')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('cluster');
            $table->index('is_featured');
        });

        Schema::create('destination_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_media');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('destination_categories');
    }
};

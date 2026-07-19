<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('property_type');
            $table->string('listing_purpose')->default('rent');
            $table->string('state');
            $table->string('city');
            $table->string('area');
            $table->string('display_address');
            $table->text('description');
            $table->unsignedBigInteger('annual_rent')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('toilets')->nullable();
            $table->unsignedTinyInteger('parking_spaces')->nullable();
            $table->decimal('size_sqm', 10, 2)->nullable();
            $table->string('furnishing_status')->nullable();
            $table->enum('availability_status', ['available', 'reserved', 'occupied', 'unavailable'])->default('available');
            $table->enum('publication_status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['publication_status', 'published_at']);
            $table->index(['state', 'city', 'area']);
            $table->index(['property_type', 'annual_rent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

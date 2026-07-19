<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('amenity_key');
            $table->string('amenity_label');
            $table->timestamps();

            $table->unique(['property_id', 'amenity_key']);
            $table->index('amenity_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create pivot table for many-to-many relationship (no custom id - Laravel handles it)
        Schema::create('business_unit_service', function (Blueprint $table) {
            $table->uuid('business_unit_id');
            $table->uuid('service_id');
            $table->tinyInteger('specialization_score')->default(50); // 1-100 scale
            $table->text('notes')->nullable(); // Additional notes about this BU's capability in this service
            $table->timestamps();

            $table->primary(['business_unit_id', 'service_id']);
            $table->foreign('business_unit_id')->references('id')->on('business_units')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Remove the old business_unit_id column from services table
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['business_unit_id']);
            $table->dropColumn('business_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the business_unit_id column
        Schema::table('services', function (Blueprint $table) {
            $table->uuid('business_unit_id')->nullable();
            $table->foreign('business_unit_id')->references('id')->on('business_units')->onDelete('set null');
        });

        Schema::dropIfExists('business_unit_service');
    }
};

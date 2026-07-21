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
        // Only create if it doesn't exist from the initial migration
        if (!Schema::hasTable('business_unit_service')) {
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
        } else {
            // Table exists from initial migration - just add the extra columns
            Schema::table('business_unit_service', function (Blueprint $table) {
                if (!Schema::hasColumn('business_unit_service', 'specialization_score')) {
                    $table->tinyInteger('specialization_score')->default(50)->after('service_id');
                }
                if (!Schema::hasColumn('business_unit_service', 'notes')) {
                    $table->text('notes')->nullable()->after('specialization_score');
                }
                if (!Schema::hasColumn('business_unit_service', 'timestamps')) {
                    $table->timestamps();
                }
            });
        }

        // Remove the old business_unit_id column from services table (if it exists)
        // This column only existed in PostgreSQL version, not in MySQL version
        try {
            if (Schema::hasColumn('services', 'business_unit_id')) {
                Schema::table('services', function (Blueprint $table) {
                    $table->dropForeign(['business_unit_id']);
                    $table->dropColumn('business_unit_id');
                });
            }
        } catch (\Exception $e) {
            // Column doesn't exist or can't be dropped, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the business_unit_id column only if it doesn't exist
        try {
            if (!Schema::hasColumn('services', 'business_unit_id')) {
                Schema::table('services', function (Blueprint $table) {
                    $table->uuid('business_unit_id')->nullable();
                    $table->foreign('business_unit_id')->references('id')->on('business_units')->onDelete('set null');
                });
            }
        } catch (\Exception $e) {
            // Column already exists or can't be added
        }

        Schema::dropIfExists('business_unit_service');
    }
};

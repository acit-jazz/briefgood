<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pitch_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->foreignUuid('business_unit_id')->constrained('business_units')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['brief_id', 'business_unit_id']);
            $table->index(['brief_id', 'status']);
        });

        Schema::create('pitch_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pitch_assignment_id')->constrained('pitch_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('role_type')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('resource_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->foreignUuid('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->unsignedSmallInteger('estimated_workload_percent')->nullable();
            $table->unsignedSmallInteger('estimated_duration_days')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['brief_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_allocations');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('pitch_responses');
        Schema::dropIfExists('pitch_assignments');
    }
};

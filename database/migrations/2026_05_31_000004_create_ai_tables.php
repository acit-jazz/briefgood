<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analysis_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->text('executive_summary')->nullable();
            $table->text('brand_overview')->nullable();
            $table->text('campaign_objective')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('timeline')->nullable();
            $table->text('budget')->nullable();
            $table->text('mandatory_requirements')->nullable();
            $table->json('recommended_business_units')->nullable();
            $table->json('recommended_services')->nullable();
            $table->json('recommended_resources')->nullable();
            $table->unsignedTinyInteger('pitch_complexity_score')->nullable();
            $table->decimal('ai_confidence_score', 5, 2)->nullable();
            $table->text('ai_reasoning')->nullable();
            $table->string('model_used')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('extracted_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('brief_id');
        });

        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ai_analysis_result_id')->constrained('ai_analysis_results')->cascadeOnDelete();
            $table->foreignUuid('business_unit_id')->nullable()->constrained('business_units')->nullOnDelete();
            $table->string('business_unit_name');
            $table->decimal('confidence', 5, 2);
            $table->json('matched_services')->nullable();
            $table->text('reasoning')->nullable();
            $table->timestamps();

            $table->index(['ai_analysis_result_id', 'confidence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_analysis_results');
    }
};

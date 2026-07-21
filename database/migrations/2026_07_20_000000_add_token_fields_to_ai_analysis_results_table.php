<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_analysis_results', function (Blueprint $table) {
            $table->unsignedInteger('prompt_tokens')->nullable()->after('model_used');
            $table->unsignedInteger('completion_tokens')->nullable()->after('prompt_tokens');
            $table->unsignedInteger('total_tokens')->nullable()->after('completion_tokens');
            $table->decimal('cost_usd', 10, 6)->nullable()->after('total_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('ai_analysis_results', function (Blueprint $table) {
            $table->dropColumn(['prompt_tokens', 'completion_tokens', 'total_tokens', 'cost_usd']);
        });
    }
};

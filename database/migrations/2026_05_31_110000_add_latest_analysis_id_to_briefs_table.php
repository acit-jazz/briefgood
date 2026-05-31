<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('briefs', function (Blueprint $table) {
            $table->foreignUuid('latest_analysis_id')
                ->nullable()
                ->after('ai_error')
                ->constrained('ai_analysis_results')
                ->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('
                UPDATE briefs
                SET latest_analysis_id = sub.id
                FROM (
                    SELECT DISTINCT ON (brief_id) id, brief_id
                    FROM ai_analysis_results
                    WHERE deleted_at IS NULL
                    ORDER BY brief_id, created_at DESC
                ) AS sub
                WHERE briefs.id = sub.brief_id
            ');
        } else {
            $briefIds = DB::table('briefs')->pluck('id');

            foreach ($briefIds as $briefId) {
                $latestId = DB::table('ai_analysis_results')
                    ->where('brief_id', $briefId)
                    ->whereNull('deleted_at')
                    ->orderByDesc('created_at')
                    ->value('id');

                if ($latestId) {
                    DB::table('briefs')->where('id', $briefId)->update(['latest_analysis_id' => $latestId]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('briefs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('latest_analysis_id');
        });
    }
};

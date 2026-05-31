<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('subject_id');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->uuid('subject_id')->nullable()->after('subject_type');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('subject_id');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->index(['subject_type', 'subject_id']);
        });
    }
};

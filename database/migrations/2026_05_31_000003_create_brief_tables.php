<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('briefs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('client_name');
            $table->string('industry')->nullable();
            $table->string('title');
            $table->date('brief_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new')->index();
            $table->string('ai_status')->default('pending')->index();
            $table->text('ai_error')->nullable();
            $table->string('primary_file_name')->nullable();
            $table->string('primary_file_path')->nullable();
            $table->string('primary_file_mime')->nullable();
            $table->unsignedBigInteger('primary_file_size')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['created_by', 'status']);
        });

        Schema::create('brief_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('disk')->default('local');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brief_files');
        Schema::dropIfExists('briefs');
    }
};

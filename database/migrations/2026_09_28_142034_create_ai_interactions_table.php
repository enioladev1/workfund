<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('refund_request_id')->references('id')->on('refund_requests')->cascadeOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->string('provider_request_id')->nullable();
            $table->enum('status', ['success', 'failed', 'timeout'])->default('success');
            $table->jsonb('structured_output')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index('refund_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};

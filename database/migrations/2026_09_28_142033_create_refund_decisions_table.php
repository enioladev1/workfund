<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per decision made on a refund request. The automated pipeline writes
     * the first row; a manual staff resolution of an escalated request adds another.
     * The latest row (by created_at) is the current decision.
     */
    public function up(): void
    {
        Schema::create('refund_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('refund_request_id')->references('id')->on('refund_requests')->cascadeOnDelete();
            $table->enum('decision', ['approved', 'denied', 'escalated'])->default('escalated');
            $table->jsonb('policy_result');
            $table->jsonb('ai_result')->nullable();
            $table->text('reasoning');
            $table->enum('decided_by', ['system', 'staff'])->default('system');
            $table->foreignId('decided_by_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index('refund_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_decisions');
    }
};

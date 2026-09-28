<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Source of truth for refund policy configuration. Cached in Redis by
     * RefundPolicyService; this table is what gets edited and invalidates the cache.
     */
    public function up(): void
    {
        Schema::create('refund_policy_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->jsonb('value');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_policy_rules');
    }
};

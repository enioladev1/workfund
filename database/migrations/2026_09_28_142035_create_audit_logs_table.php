<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only. No updated_at column, and the application never issues
     * UPDATE/DELETE against this table (enforced in AuditLog model + tests).
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->string('action');
            $table->enum('actor_type', ['system', 'customer', 'staff'])->default('system');
            $table->string('actor_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

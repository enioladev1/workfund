<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreignUuid('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->foreignUuid('order_item_id')->nullable()->references('id')->on('order_items')->nullOnDelete();
            $table->bigInteger('requested_amount_minor');
            $table->char('currency', 3)->default('USD');
            $table->enum('reason', ['damaged_item', 'incorrect_item', 'not_as_described', 'changed_mind', 'no_longer_needed', 'other'])->default('other');
            $table->text('customer_message');
            $table->enum('status', ['pending', 'approved', 'denied', 'escalated'])->default('pending');
            $table->string('idempotency_key')->nullable();
            $table->timestamps();

            $table->unique('idempotency_key');
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};

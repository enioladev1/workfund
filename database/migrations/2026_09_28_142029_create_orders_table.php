<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->string('order_number');
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'partially_refunded'])->default('delivered');
            $table->char('currency', 3)->default('USD');
            $table->bigInteger('total_minor');
            $table->boolean('is_final_sale')->default(false);
            $table->timestamp('ordered_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique('order_number');
            $table->index('customer_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

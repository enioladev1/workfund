<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->bigInteger('unit_price_minor');
            $table->bigInteger('total_price_minor');
            $table->boolean('is_final_sale')->default(false);
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

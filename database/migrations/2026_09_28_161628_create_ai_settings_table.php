<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-editable AI settings (currently just the selected OpenRouter model).
     * The API key stays in .env only; nothing here is ever a secret.
     */
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->jsonb('value');
            $table->timestamps();

            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};

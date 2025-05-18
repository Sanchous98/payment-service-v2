<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('api_key');
            $table->text('webhook_signing_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_credentials');
    }
};
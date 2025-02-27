<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nuvei_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('merchant_id');
            $table->text('site_id');
            $table->text('secret_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuvei_credentials');
    }
};

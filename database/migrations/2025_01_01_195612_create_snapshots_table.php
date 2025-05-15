<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->binary('aggregate_root_id', 16, true)->primary();
            $table->integer('aggregate_root_version');
            $table->json('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->id();
            $table->binary('event_id', 16, true);
            $table->binary('aggregate_root_id', 16, true)->index('reconstitution');
            $table->integer('version')->unsigned();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['aggregate_root_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_events');
    }
};

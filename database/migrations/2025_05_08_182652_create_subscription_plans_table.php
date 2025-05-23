<?php

use App\Enum\IntervalUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->unsignedSmallInteger('interval_count');
            $table->enum('interval_unit', array_column(IntervalUnit::cases(), 'value'));
            $table->string('merchant_descriptor', 22)->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

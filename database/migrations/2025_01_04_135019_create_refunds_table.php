<?php

use App\Models\PaymentIntent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PaymentSystem\Enum\RefundStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->unsignedInteger('fee_amount')->nullable();
            $table->char('fee_currency', 3)->nullable();
            $table->enum('status', array_column(RefundStatusEnum::cases(), 'value'));
            $table->foreignIdFor(PaymentIntent::class)->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PaymentSystem\Enum\PaymentIntentStatusEnum;
use PaymentSystem\Laravel\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->unsignedInteger('fee_amount')->nullable();
            $table->char('fee_currency', 3)->nullable();
            $table->string('description')->default('');
            $table->string('merchant_descriptor', 22)->default('');
            $table->enum('status', array_column(PaymentIntentStatusEnum::cases(), 'value'));

            $table->foreignIdFor(Account::class)->constrained();
            $table->uuidMorphs('tender');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};

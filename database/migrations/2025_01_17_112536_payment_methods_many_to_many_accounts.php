<?php

use App\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PaymentSystem\Laravel\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_payment_method', function (Blueprint $table) {
            $table->foreignIdFor(Account::class)->constrained();
            $table->foreignIdFor(PaymentMethod::class)->constrained();

            $table->unique(['account_id', 'payment_method_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payment_method');
    }
};

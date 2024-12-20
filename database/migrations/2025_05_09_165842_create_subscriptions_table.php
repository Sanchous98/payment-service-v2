<?php

use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PaymentSystem\Enum\SubscriptionStatusEnum;
use PaymentSystem\Laravel\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', array_column(SubscriptionStatusEnum::cases(), 'value'));
            $table->date('ends_at');
            $table->foreignIdFor(SubscriptionPlan::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PaymentMethod::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('payment_intents', function (Blueprint $table) {
            $table->foreignIdFor(Subscription::class)->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_intents', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn('subscription_id');
        });
        Schema::dropIfExists('subscriptions');
    }
};

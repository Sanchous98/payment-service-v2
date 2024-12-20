<?php

namespace App\Console\Commands;

use App\Models\Refund;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use PaymentSystem\Enum\RefundStatusEnum;
use PaymentSystem\Laravel\Jobs\CancelRefundJob;
use PaymentSystem\Laravel\Uuid;

use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

class RefundCancel extends Command implements PromptsForMissingInput
{
    protected $signature = 'payments:refund:cancel
                            {refund : Refund}';

    protected $description = 'Cancel refund';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'refund' => fn() => select(
                label: 'Refund',
                options: Refund::query()
                    ->whereIn('status', [RefundStatusEnum::CREATED, RefundStatusEnum::REQUIRES_ACTION])
                    ->pluck('id'),
            )
        ];
    }

    public function __invoke(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(new CancelRefundJob(
            Uuid::fromString($this->argument('refund')),
            Refund::with(['paymentIntent', 'paymentIntent.account'])->find($this->argument('refund'))->paymentIntent->account
        ));

        info("Refund {$this->argument('refund')} cancelled successfully");
    }
}

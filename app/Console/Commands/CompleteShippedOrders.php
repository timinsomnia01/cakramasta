<?php

namespace App\Console\Commands;

use App\Actions\Orders\CompleteOrder;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class CompleteShippedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:complete-shipped';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Selesaikan otomatis pesanan yang sudah dikirim tetapi tidak dikonfirmasi pembeli';

    public function handle(CompleteOrder $completeOrder): int
    {
        $days = (int) config('marketplace.auto_complete_days');
        $completed = 0;

        Order::query()
            ->where('status', OrderStatus::Shipped)
            ->where('shipped_at', '<', now()->subDays($days))
            ->each(function (Order $order) use ($completeOrder, $days, &$completed) {
                $completeOrder->handle($order, null, "Pesanan selesai otomatis setelah {$days} hari sejak dikirim.");
                $completed++;
            });

        $this->info("{$completed} pesanan diselesaikan.");

        return self::SUCCESS;
    }
}

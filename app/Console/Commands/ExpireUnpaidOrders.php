<?php

namespace App\Console\Commands;

use App\Actions\Orders\CancelOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ExpireUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan pesanan yang melewati batas waktu pembayaran dan kembalikan stoknya';

    public function handle(CancelOrder $cancelOrder): int
    {
        $cancelled = 0;

        Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->whereHas('payments', fn ($query) => $query
                ->where('status', PaymentStatus::Pending)
                ->where('expires_at', '<', now()))
            ->each(function (Order $order) use ($cancelOrder, &$cancelled) {
                try {
                    $cancelOrder->handle($order, null, 'Pembayaran tidak dilakukan dalam batas waktu.');
                    $cancelled++;
                } catch (ValidationException) {
                    // Status berubah di tengah proses (misalnya baru saja dibayar), lewati.
                }
            });

        $this->info("{$cancelled} pesanan dibatalkan.");

        return self::SUCCESS;
    }
}

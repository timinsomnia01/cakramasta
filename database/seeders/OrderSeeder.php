<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\Support\CatalogData;
use Database\Seeders\Support\IndonesianData;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pesanan lengkap dengan item, pembayaran, log status, dan ulasan.
 *
 * Alur status: pending_payment -> waiting_verification -> paid -> processing -> shipped -> completed
 * (atau cancelled). Waktu tiap tahap dibuat berurutan dan selalu sebelum "sekarang".
 */
class OrderSeeder extends Seeder
{
    /**
     * Jumlah pesanan menurut status.
     *
     * @var array<string, int>
     */
    private const PLAN = [
        'completed' => 28,
        'shipped' => 4,
        'processing' => 4,
        'paid' => 3,
        'waiting_verification' => 3,
        'pending_payment' => 3,
        'cancelled' => 3,
    ];

    /**
     * @var array<string, string>
     */
    private const COURIER_PREFIX = [
        'JNE Reguler' => 'CGK',
        'J&T Express' => 'JP',
        'SiCepat REG' => '00',
    ];

    private int $cancelCounter = 0;

    public function run(): void
    {
        $buyers = User::role(UserRole::User)->whereDoesntHave('seller')->get()->all();
        $verifiers = User::role(UserRole::Verifikator)->get()->all();

        $sellers = Seller::approved()
            ->with(['user', 'products' => fn ($query) => $query->approved()
                ->where('stock', '>', 0)
                ->with(['variants', 'images'])])
            ->get()
            ->filter(fn (Seller $seller) => $seller->products->isNotEmpty())
            ->values()
            ->all();

        $plan = [];
        foreach (self::PLAN as $status => $count) {
            $plan = array_merge($plan, array_fill(0, $count, OrderStatus::from($status)));
        }

        foreach (fake()->shuffle($plan) as $status) {
            $this->createOrder(
                $status,
                fake()->randomElement($buyers),
                fake()->randomElement($sellers),
                fake()->randomElement($verifiers),
            );
        }

        $this->updateSoldCounts();
    }

    private function createOrder(OrderStatus $status, User $buyer, Seller $seller, User $verifier): void
    {
        $createdAt = $this->createdAtFor($status);
        $timeline = $this->timeline($status, $createdAt);
        $lines = $this->pickLines($seller);

        $subtotal = array_sum(array_column($lines, 'subtotal'));
        $shipping = fake()->randomElement([0, 8000, 10000, 12000, 15000]);
        $platformFee = (int) round($subtotal * 0.05);
        $tax = (int) round($subtotal * 0.005);
        $shipped = isset($timeline[OrderStatus::Shipped->value]);
        $courier = $shipped ? fake()->randomElement(array_keys(self::COURIER_PREFIX)) : null;
        $cancelled = $status === OrderStatus::Cancelled;
        $lastEventAt = max($timeline);

        $order = Order::factory()->create([
            'code' => IndonesianData::code('UM', $createdAt),
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => $status,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'platform_fee' => $platformFee,
            'tax_amount' => $tax,
            'total' => $subtotal + $shipping,
            'seller_income' => $subtotal - $platformFee - $tax,
            'recipient_name' => $buyer->name,
            'recipient_phone' => $buyer->phone,
            'shipping_address' => $buyer->address,
            'courier' => $courier,
            'tracking_number' => $courier ? self::COURIER_PREFIX[$courier].fake()->numerify('##########') : null,
            'notes' => fake()->boolean(25)
                ? fake()->randomElement(['Tolong dibungkus bubble wrap ya.', 'Kirim sore hari saja kak.', 'Titip ke satpam kos kalau saya tidak di tempat.'])
                : null,
            'paid_at' => $timeline[OrderStatus::Paid->value] ?? null,
            'shipped_at' => $timeline[OrderStatus::Shipped->value] ?? null,
            'completed_at' => $timeline[OrderStatus::Completed->value] ?? null,
            'cancelled_at' => $timeline[OrderStatus::Cancelled->value] ?? null,
            'cancel_reason' => $cancelled
                ? ($this->cancelCounter % 2 === 0 ? 'Pembayaran ditolak: nominal tidak sesuai tagihan.' : 'Pembayaran tidak dilakukan dalam batas waktu 24 jam.')
                : null,
            'created_at' => $createdAt,
            'updated_at' => $lastEventAt,
        ]);

        $items = [];
        foreach ($lines as $line) {
            $items[] = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'product_variant_id' => $line['variant']?->id,
                'product_name' => $line['product']->name,
                'variant_name' => $line['variant']?->name,
                'product_image' => $line['product']->images->first()?->path,
                'price' => $line['price'],
                'quantity' => $line['quantity'],
                'subtotal' => $line['subtotal'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->createPayment($order, $timeline, $verifier);
        $this->createLogs($order, $timeline, $buyer, $seller, $verifier);

        if ($status === OrderStatus::Completed && fake()->boolean(80)) {
            $this->createReviews($order, $items, $buyer, $timeline[OrderStatus::Completed->value]);
        }
    }

    /**
     * Waktu pembuatan pesanan, dipilih agar seluruh tahapnya selesai sebelum "sekarang".
     */
    private function createdAtFor(OrderStatus $status): CarbonInterface
    {
        $base = match ($status) {
            OrderStatus::Completed => now()->subDays(fake()->numberBetween(10, 70)),
            OrderStatus::Shipped => now()->subDays(fake()->numberBetween(4, 8)),
            OrderStatus::Processing => now()->subDays(fake()->numberBetween(2, 5)),
            OrderStatus::Paid => now()->subDays(fake()->numberBetween(1, 3)),
            OrderStatus::WaitingVerification => now()->subHours(fake()->numberBetween(5, 40)),
            OrderStatus::PendingPayment => now()->subHours(fake()->numberBetween(1, 20)),
            default => now()->subDays(fake()->numberBetween(3, 40)),
        };

        return $base->subMinutes(fake()->numberBetween(0, 600));
    }

    /**
     * Daftar tahap beserta waktunya, berurutan sesuai alur pesanan.
     *
     * @return array<string, CarbonInterface>
     */
    private function timeline(OrderStatus $status, CarbonInterface $createdAt): array
    {
        $path = match ($status) {
            OrderStatus::PendingPayment => [],
            OrderStatus::WaitingVerification => [OrderStatus::WaitingVerification],
            OrderStatus::Paid => [OrderStatus::WaitingVerification, OrderStatus::Paid],
            OrderStatus::Processing => [OrderStatus::WaitingVerification, OrderStatus::Paid, OrderStatus::Processing],
            OrderStatus::Shipped => [OrderStatus::WaitingVerification, OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped],
            OrderStatus::Completed => [OrderStatus::WaitingVerification, OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed],
            // Pembatalan bergantian: pembayaran kedaluwarsa, atau bukti bayar ditolak.
            OrderStatus::Cancelled => ++$this->cancelCounter % 2 === 0
                ? [OrderStatus::WaitingVerification, OrderStatus::Cancelled]
                : [OrderStatus::Cancelled],
            default => [],
        };

        $minutes = [
            OrderStatus::WaitingVerification->value => [10, 180],
            OrderStatus::Paid->value => [60, 720],
            OrderStatus::Processing->value => [120, 1440],
            OrderStatus::Shipped->value => [240, 2160],
            OrderStatus::Completed->value => [2880, 8640],
            OrderStatus::Cancelled->value => [1500, 2000],
        ];

        $timeline = [OrderStatus::PendingPayment->value => $createdAt->copy()];
        $at = $createdAt->copy();

        foreach ($path as $step) {
            [$min, $max] = $minutes[$step->value];
            $at = $at->copy()->addMinutes(fake()->numberBetween($min, $max));
            $timeline[$step->value] = $at;
        }

        return $timeline;
    }

    /**
     * Pilih 1 sampai 3 produk milik satu penjual beserta varian dan jumlahnya.
     *
     * @return list<array{product: Product, variant: \App\Models\ProductVariant|null, price: int, quantity: int, subtotal: int}>
     */
    private function pickLines(Seller $seller): array
    {
        $count = min($seller->products->count(), fake()->numberBetween(1, 3));
        $lines = [];

        foreach (fake()->randomElements($seller->products->all(), $count) as $product) {
            $variants = $product->variants->where('is_active', true)->values()->all();
            $variant = $variants === [] ? null : fake()->randomElement($variants);
            $price = $variant?->price ?? $product->price;
            $quantity = $price < 60000 ? fake()->numberBetween(1, 4) : fake()->numberBetween(1, 2);

            $lines[] = [
                'product' => $product,
                'variant' => $variant,
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, CarbonInterface>  $timeline
     */
    private function createPayment(Order $order, array $timeline, User $verifier): void
    {
        $method = fake()->randomElement(['qris', 'qris', 'qris', 'bank_transfer']);
        $uploadedAt = $timeline[OrderStatus::WaitingVerification->value] ?? null;
        $paidAt = $timeline[OrderStatus::Paid->value] ?? null;
        $createdAt = $timeline[OrderStatus::PendingPayment->value];

        $status = match (true) {
            $paidAt !== null => PaymentStatus::Verified,
            $order->status === OrderStatus::WaitingVerification => PaymentStatus::WaitingVerification,
            $order->status === OrderStatus::Cancelled && $uploadedAt !== null => PaymentStatus::Rejected,
            $order->status === OrderStatus::Cancelled => PaymentStatus::Expired,
            default => PaymentStatus::Pending,
        };

        $proof = $uploadedAt !== null
            ? PlaceholderImage::proof("payments/proofs/{$order->code}.svg", $order->code, $order->total, $method)
            : null;

        Payment::factory()->create([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $order->total,
            'status' => $status,
            'proof_image' => $proof,
            'verified_by' => in_array($status, [PaymentStatus::Verified, PaymentStatus::Rejected], true) ? $verifier->id : null,
            'verified_at' => $status === PaymentStatus::Verified
                ? $paidAt
                : ($status === PaymentStatus::Rejected ? $timeline[OrderStatus::Cancelled->value] : null),
            'rejection_reason' => $status === PaymentStatus::Rejected ? 'Nominal pada bukti pembayaran tidak sesuai tagihan.' : null,
            'paid_at' => $uploadedAt,
            'expires_at' => $createdAt->copy()->addDay(),
            'created_at' => $createdAt,
            'updated_at' => max($timeline),
        ]);
    }

    /**
     * @param  array<string, CarbonInterface>  $timeline
     */
    private function createLogs(Order $order, array $timeline, User $buyer, Seller $seller, User $verifier): void
    {
        $notes = [
            OrderStatus::PendingPayment->value => ['Pesanan dibuat, menunggu pembayaran.', $buyer->id],
            OrderStatus::WaitingVerification->value => ['Bukti pembayaran diunggah.', $buyer->id],
            OrderStatus::Paid->value => ['Pembayaran terverifikasi.', $verifier->id],
            OrderStatus::Processing->value => ['Pesanan sedang dikemas penjual.', $seller->user_id],
            OrderStatus::Shipped->value => ["Pesanan dikirim via {$order->courier}, resi {$order->tracking_number}.", $seller->user_id],
            OrderStatus::Completed->value => ['Pesanan diterima pembeli.', $buyer->id],
            OrderStatus::Cancelled->value => [$order->cancel_reason, null],
        ];

        foreach ($timeline as $status => $at) {
            [$note, $changedBy] = $notes[$status];

            OrderStatusLog::factory()->create([
                'order_id' => $order->id,
                'status' => $status,
                'note' => $note,
                'changed_by' => $changedBy,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }

    /**
     * @param  list<OrderItem>  $items
     */
    private function createReviews(Order $order, array $items, User $buyer, CarbonInterface $completedAt): void
    {
        $ratings = array_merge(array_fill(0, 9, 5), array_fill(0, 5, 4), [3, 3, 2, 1]);
        $comments = CatalogData::reviewComments();

        foreach ($items as $item) {
            $rating = fake()->randomElement($ratings);
            $comment = str_replace(
                '{produk}',
                Str::words($item->product_name, 2, ''),
                fake()->randomElement($comments[$rating]),
            );
            $reviewedAt = $completedAt->copy()->addHours(fake()->numberBetween(2, 72));

            Review::factory()->create([
                'product_id' => $item->product_id,
                'user_id' => $buyer->id,
                'order_item_id' => $item->id,
                'rating' => $rating,
                'comment' => $comment,
                'images' => null,
                'created_at' => $reviewedAt,
                'updated_at' => $reviewedAt,
            ]);
        }
    }

    /**
     * Isi sold_count: penjualan dari pesanan selesai ditambah angka dasar (penjualan sebelum
     * data demo) supaya kartu produk tidak terlihat sepi.
     */
    private function updateSoldCounts(): void
    {
        $sold = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->groupBy('order_items.product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total'))
            ->pluck('total', 'product_id');

        Product::approved()->pluck('id')->each(function (int $id) use ($sold) {
            Product::whereKey($id)->update([
                'sold_count' => (int) ($sold[$id] ?? 0) + fake()->numberBetween(3, 120),
            ]);
        });
    }
}

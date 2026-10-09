<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /** Alur status per status akhir -> dipakai membentuk order_logs */
    private const PATHS = [
        'completed' => ['pending_payment', 'paid', 'processing', 'completed'],
        'processing' => ['pending_payment', 'paid', 'processing'],
        'paid' => ['pending_payment', 'paid'],
        'pending_payment' => ['pending_payment'],
        'cancelled' => ['pending_payment', 'cancelled'],
    ];

    private const COMMENTS = [
        'Barang sesuai deskripsi, penjual ramah.',
        'Pengiriman cepat, kualitas bagus.',
        'Lumayan, sesuai harga.',
        'Recommended seller!',
        'Packing rapi, barang aman sampai tujuan.',
    ];

    public function run(): void
    {
        $verifikator = User::where('email', 'verifikator@umart.test')->first();
        $buyers = User::where('role', 'user')->whereDoesntHave('sellerProfile')->get();
        $products = Product::approved()->with('variants')->get()->groupBy('seller_profile_id');

        $statuses = ['completed', 'completed', 'completed', 'completed', 'processing', 'paid', 'pending_payment', 'cancelled'];

        foreach (range(1, 30) as $i) {
            $buyer = $buyers->random();
            $sellerId = $products->keys()->random();
            $picked = $products[$sellerId]->random(min(rand(1, 3), $products[$sellerId]->count()));
            $final = $statuses[array_rand($statuses)];
            $createdAt = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1000));

            // --- item ---
            $lines = [];
            foreach ($picked as $product) {
                $variant = $product->variants->isNotEmpty() ? $product->variants->random() : null;
                $qty = rand(1, 3);
                $price = $variant?->price ?? $product->price;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant?->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $price * $qty,
                ];
            }

            $subtotal = collect($lines)->sum('subtotal');
            $fee = (int) round($subtotal * 0.02); // potongan platform 2%

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'seller_profile_id' => $sellerId,
                'subtotal' => $subtotal,
                'admin_fee' => $fee,
                'total' => $subtotal,
                'seller_earning' => $subtotal - $fee,
                'status' => $final,
                'expired_at' => $createdAt->copy()->addDay(),
                'paid_at' => in_array($final, ['paid', 'processing', 'completed']) ? $createdAt->copy()->addMinutes(15) : null,
                'completed_at' => $final === 'completed' ? $createdAt->copy()->addDays(2) : null,
                'cancelled_at' => $final === 'cancelled' ? $createdAt->copy()->addHour() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $items = collect($lines)->map(fn ($l) => $order->items()->create($l));

            // --- payment ---
            $order->payments()->create([
                'method' => 'qris',
                'provider_reference' => 'SEED-'.strtoupper(substr(md5((string) $order->id), 0, 10)),
                'amount' => $order->total,
                'status' => match ($final) {
                    'cancelled' => PaymentStatus::Expired,
                    'pending_payment' => PaymentStatus::Pending,
                    default => PaymentStatus::Paid,
                },
                'paid_at' => $order->paid_at,
                'expired_at' => $order->expired_at,
            ]);

            // --- log pembelian ---
            $path = self::PATHS[$final];
            foreach ($path as $step => $to) {
                $order->logs()->create([
                    'actor_id' => $step === 0 ? $buyer->id : $verifikator->id,
                    'from_status' => $path[$step - 1] ?? null,
                    'to_status' => $to,
                    'note' => $step === 0 ? 'Pesanan dibuat' : null,
                    'created_at' => $createdAt->copy()->addMinutes($step * 30),
                ]);
            }

            // --- ulasan & sold_count untuk pesanan selesai ---
            if ($final === 'completed') {
                foreach ($items as $item) {
                    $item->product?->increment('sold_count', $item->quantity);

                    if (rand(1, 100) <= 70) {
                        ProductReview::create([
                            'product_id' => $item->product_id,
                            'user_id' => $buyer->id,
                            'order_item_id' => $item->id,
                            'rating' => rand(3, 5),
                            'comment' => self::COMMENTS[array_rand(self::COMMENTS)],
                        ]);
                    }
                }
            }
        }

        $this->seedWithdrawalsAndBalances();
    }

    /** Buat contoh pencairan dan hitung saldo penjual agar konsisten dengan order selesai. */
    private function seedWithdrawalsAndBalances(): void
    {
        $verifikator = User::where('email', 'verifikator@umart.test')->first();

        foreach (SellerProfile::approved()->get() as $seller) {
            $earned = (int) Order::where('seller_profile_id', $seller->id)
                ->where('status', OrderStatus::Completed)
                ->sum('seller_earning');

            $approved = (int) (floor($earned * 0.4 / 1000) * 1000);
            $pending = (int) (floor($earned * 0.2 / 1000) * 1000);

            if ($approved > 0) {
                $seller->withdrawals()->create([
                    'amount' => $approved,
                    'status' => WithdrawalStatus::Approved,
                    'seller_note' => 'Pencairan ke rekening/e-wallet terdaftar.',
                    'verifier_note' => 'Sudah ditransfer.',
                    'proof_path' => 'seed/withdrawals/bukti-transfer.jpg',
                    'processed_by' => $verifikator->id,
                    'processed_at' => now()->subDays(2),
                ]);
            }

            if ($pending > 0) {
                $seller->withdrawals()->create([
                    'amount' => $pending,
                    'status' => WithdrawalStatus::Pending,
                    'seller_note' => 'Mohon dicairkan.',
                ]);
            }

            // saldo tersedia = pendapatan - pencairan yang disetujui - yang sedang diajukan
            $seller->update(['balance' => max(0, $earned - $approved - $pending)]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 order = 1 penjual (checkout multi-penjual dipecah menjadi beberapa order)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // contoh: UM-20261009-ABC123
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('seller_profile_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('admin_fee')->default(0);     // potongan platform / pajak
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('seller_earning')->default(0); // subtotal - admin_fee
            $table->string('status', 30)->default('pending_payment')->index();
            $table->text('buyer_note')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['seller_profile_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            // snapshot agar histori tidak berubah saat produk diedit
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30)->nullable();       // qris, va_bca, dll
            $table->string('provider_reference')->nullable()->index(); // id transaksi gateway
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending')->index();
            $table->string('proof_path')->nullable();
            $table->json('payload')->nullable();             // raw response/webhook
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        // Log perubahan status pembelian (dasar "Log Pembelian" + export Excel)
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

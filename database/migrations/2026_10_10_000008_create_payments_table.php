<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // qris | bank_transfer | gateway, dll
            $table->unsignedBigInteger('amount');

            // pending | waiting_verification | verified | rejected | expired  (lihat App\Enums\PaymentStatus)
            $table->string('status')->default('pending')->index();

            // Pembayaran manual (upload bukti) yang diverifikasi verifikator
            $table->string('proof_image')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Payment gateway (Midtrans / Xendit / dll) bila dipakai
            $table->string('gateway_reference')->nullable()->index();
            $table->json('gateway_payload')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

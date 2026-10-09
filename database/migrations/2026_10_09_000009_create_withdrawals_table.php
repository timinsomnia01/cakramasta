<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pencairan hasil jual (diverifikasi oleh verifikator/admin)
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('seller_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending')->index();
            $table->text('seller_note')->nullable();
            $table->text('verifier_note')->nullable();
            $table->string('proof_path')->nullable(); // bukti transfer
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

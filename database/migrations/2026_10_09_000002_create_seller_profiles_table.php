<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->text('store_description')->nullable();
            $table->string('nik', 32);               // nomor KTP
            $table->string('ktp_photo');             // path foto KTP
            $table->string('selfie_photo');          // foto diri/selfie bersama KTP
            $table->string('qris_image');            // path gambar QRIS penjual
            $table->string('status', 20)->default('pending')->index(); // pending|approved|rejected
            $table->text('rejection_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('balance')->default(0); // saldo hasil jual yang bisa dicairkan (Rp)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};

<?php

namespace App\Http\Controllers\Verification;

use App\Enums\PaymentStatus;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Withdrawal;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Antrean verifikasi: jumlah yang menunggu untuk setiap jenis.
     */
    public function index(): Response
    {
        return Inertia::render('verification/dashboard', [
            'queues' => [
                'sellers' => Seller::query()->pending()->count(),
                'products' => Product::query()->pending()->count(),
                'payments' => Payment::query()->waitingVerification()->count(),
                'withdrawals' => Withdrawal::query()->where('status', WithdrawalStatus::Pending)->count(),
            ],
            'verifiedToday' => [
                'payments' => Payment::query()->where('status', PaymentStatus::Verified)->whereDate('verified_at', today())->count(),
                'sellers' => Seller::query()->whereDate('verified_at', today())->count(),
                'products' => Product::query()->whereDate('verified_at', today())->count(),
            ],
        ]);
    }
}

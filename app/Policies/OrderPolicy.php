<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Pembeli, penjual pemilik order, verifikator, dan admin boleh melihat order.
     */
    public function view(User $user, Order $order): bool
    {
        return $this->isBuyer($user, $order)
            || $this->isSeller($user, $order)
            || $user->hasRole(UserRole::Admin, UserRole::Verifikator);
    }

    /**
     * Aksi khusus pembeli: bayar, batalkan, konfirmasi diterima, beri ulasan.
     */
    public function buyerAction(User $user, Order $order): bool
    {
        return $this->isBuyer($user, $order);
    }

    /**
     * Aksi khusus penjual pemilik order: proses dan kirim pesanan.
     */
    public function sellerAction(User $user, Order $order): bool
    {
        return $this->isSeller($user, $order);
    }

    private function isBuyer(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    private function isSeller(User $user, Order $order): bool
    {
        return $order->seller?->user_id === $user->id;
    }
}

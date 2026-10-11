<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Pemilik toko dan admin boleh mengelola (ubah, hapus, aktif/nonaktifkan) produk.
     */
    public function manage(User $user, Product $product): bool
    {
        return $user->isAdmin() || $product->seller?->user_id === $user->id;
    }
}

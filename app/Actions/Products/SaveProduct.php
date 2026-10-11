<?php

namespace App\Actions\Products;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaveProduct
{
    /**
     * Buat atau perbarui produk beserta gambar dan variannya dalam satu transaksi.
     *
     * Aturan status:
     *  - Produk baru masuk "pending" (atau "draft" bila diminta).
     *  - Draft / ditolak yang disimpan ulang menjadi "pending" (diajukan kembali).
     *  - Produk "approved" yang nama, deskripsi, kategori, gambar, atau variannya berubah kembali
     *    ke "pending" untuk diverifikasi ulang. Perubahan harga dan stok saja tidak memerlukan verifikasi.
     *  - Admin / verifikator boleh menentukan status langsung lewat $data['status'].
     *
     * @param  array<string, mixed>  $data  Data tervalidasi.
     * @param  list<UploadedFile>  $newImages
     */
    public function handle(Seller $seller, ?Product $product, array $data, array $newImages = [], ?User $actor = null): Product
    {
        return DB::transaction(function () use ($seller, $product, $data, $newImages, $actor) {
            $variants = array_values($data['variants'] ?? []);
            $hasVariants = $variants !== [];
            $isNew = $product === null;
            $product ??= new Product;

            $product->fill([
                'seller_id' => $seller->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'],
                'price' => $hasVariants ? min(array_column($variants, 'price')) : $data['price'],
                'stock' => $hasVariants ? $this->variantStock($variants) : $data['stock'],
                'weight' => $data['weight'],
                'condition' => $data['condition'],
            ]);

            $contentChanged = ! $isNew && $product->isDirty(['name', 'description', 'category_id']);

            $product->save();

            $imagesChanged = $this->syncImages(
                $product,
                $data['keep_image_ids'] ?? null,
                $newImages,
                isset($data['primary_image_id']) ? (int) $data['primary_image_id'] : null,
            );
            $variantsChanged = $this->syncVariants($product, $variants);

            $this->applyStatus(
                $product,
                $isNew,
                $data['status'] ?? null,
                $contentChanged || $imagesChanged || $variantsChanged,
                $actor,
            );

            return $product->refresh();
        });
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     */
    private function variantStock(array $variants): int
    {
        return (int) collect($variants)
            ->filter(fn (array $variant) => $variant['is_active'] ?? true)
            ->sum('stock');
    }

    /**
     * @param  list<int>|null  $keepIds  null = pertahankan semua gambar lama.
     * @param  list<UploadedFile>  $newImages
     * @return bool Apakah kumpulan gambar berubah (ditambah / dihapus).
     */
    private function syncImages(Product $product, ?array $keepIds, array $newImages, ?int $primaryId): bool
    {
        $existing = $product->images()->get();
        $keep = $keepIds === null ? $existing : $existing->whereIn('id', $keepIds);
        $changed = false;

        foreach ($existing->diff($keep) as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
            $changed = true;
        }

        /** @var Collection<int, \App\Models\ProductImage> $ordered */
        $ordered = $keep->sortBy('sort_order')->values();

        if ($primaryId !== null && $primary = $ordered->firstWhere('id', $primaryId)) {
            $ordered = $ordered->reject(fn ($image) => $image->id === $primary->id)->prepend($primary)->values();
        }

        foreach ($newImages as $file) {
            $ordered->push($product->images()->create([
                'path' => $file->store('products', 'public'),
                'is_primary' => false,
                'sort_order' => 999,
            ]));
            $changed = true;
        }

        foreach ($ordered->values() as $position => $image) {
            if ($image->sort_order !== $position || $image->is_primary !== ($position === 0)) {
                $image->update(['sort_order' => $position, 'is_primary' => $position === 0]);
            }
        }

        return $changed;
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     * @return bool Apakah daftar / nama varian berubah.
     */
    private function syncVariants(Product $product, array $variants): bool
    {
        $existing = $product->variants()->get()->keyBy('id');
        $keptIds = [];
        $changed = false;

        foreach ($variants as $row) {
            $attributes = [
                'name' => $row['name'],
                'sku' => $row['sku'] ?? null,
                'price' => $row['price'],
                'stock' => $row['stock'],
                'is_active' => $row['is_active'] ?? true,
            ];

            $variant = isset($row['id']) ? $existing->get((int) $row['id']) : null;

            if ($variant) {
                $changed = $changed || $variant->name !== $attributes['name'];
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create($attributes);
                $changed = true;
            }

            $keptIds[] = $variant->id;
        }

        foreach ($existing->reject(fn ($variant) => in_array($variant->id, $keptIds, true)) as $stale) {
            $stale->delete();
            $changed = true;
        }

        return $changed;
    }

    private function applyStatus(Product $product, bool $isNew, ?string $requested, bool $needsReview, ?User $actor): void
    {
        $current = $product->status;
        $isStaff = $actor?->hasRole(UserRole::Admin, UserRole::Verifikator) ?? false;

        $staffStatus = $isStaff && $requested !== null ? ProductStatus::tryFrom($requested) : null;

        $next = match (true) {
            $staffStatus !== null => $staffStatus,
            $isNew => $requested === 'draft' ? ProductStatus::Draft : ProductStatus::Pending,
            in_array($current, [ProductStatus::Draft, ProductStatus::Rejected], true) => $requested === 'draft' ? ProductStatus::Draft : ProductStatus::Pending,
            $current === ProductStatus::Approved && $needsReview => ProductStatus::Pending,
            default => $current,
        };

        if ($next === $current && ! $isNew) {
            return;
        }

        $attributes = ['status' => $next, 'rejection_reason' => null];

        if ($next === ProductStatus::Approved) {
            $attributes += ['verified_by' => $actor?->id, 'verified_at' => now()];
        } elseif ($next === ProductStatus::Pending || $next === ProductStatus::Draft) {
            $attributes += ['verified_by' => null, 'verified_at' => null];
        }

        $product->forceFill($attributes)->save();
    }
}

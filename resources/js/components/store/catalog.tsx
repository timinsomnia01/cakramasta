import { Link } from '@inertiajs/react';
import { ImageOff, Search, X } from 'lucide-react'; // Tambahkan ImageOff di sini
import type { ReactNode } from 'react';
import { NativeSelect } from '@/components/marketplace/native-select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ProductCard, type ProductCardData } from '@/components/store/product-card';
import { formatNumber, formatRupiah } from '@/lib/format'; // Tambahkan formatRupiah di sini
import type { Paginated } from '@/types';

export const SORT_LABELS: Record<string, string> = {
    terbaru: 'Terbaru',
    terlaris: 'Terlaris',
    populer: 'Terpopuler',
    termurah: 'Harga terendah',
    termahal: 'Harga tertinggi',
};

type Filter = {
    values: Record<string, string>;
    set: (key: string, value: string, debounce?: boolean) => void;
    reset: () => void;
    active: boolean;
};

/** Baris filter: kata kunci, rentang harga, urutan. `children` untuk filter tambahan (mis. kategori). */
export function CatalogToolbar({ filter, sorts, children }: { filter: Filter; sorts: string[]; children?: ReactNode }) {
    return (
        <div className="flex flex-wrap items-end gap-2">
            <div className="relative min-w-48 flex-1">
                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden />
                <Input
                    value={filter.values.q ?? ''}
                    onChange={(event) => filter.set('q', event.target.value, true)}
                    placeholder="Kata kunci"
                    aria-label="Kata kunci"
                    className="pl-9"
                />
            </div>
            {children}
            <Input
                type="number"
                min={0}
                inputMode="numeric"
                value={filter.values.min_price ?? ''}
                onChange={(event) => filter.set('min_price', event.target.value, true)}
                placeholder="Harga min"
                aria-label="Harga minimum"
                className="w-28"
            />
            <Input
                type="number"
                min={0}
                inputMode="numeric"
                value={filter.values.max_price ?? ''}
                onChange={(event) => filter.set('max_price', event.target.value, true)}
                placeholder="Harga maks"
                aria-label="Harga maksimum"
                className="w-28"
            />
            <NativeSelect
                value={filter.values.sort || 'terbaru'}
                onChange={(event) => filter.set('sort', event.target.value)}
                aria-label="Urutkan"
                className="w-auto"
            >
                {sorts.map((sort) => (
                    <option key={sort} value={sort}>
                        {SORT_LABELS[sort] ?? sort}
                    </option>
                ))}
            </NativeSelect>
            {filter.active && (
                <Button variant="ghost" size="sm" onClick={filter.reset}>
                    <X aria-hidden /> Reset
                </Button>
            )}
        </div>
    );
}

export function ProductGrid({ products }: { products: Paginated<ProductCardData> }) {
    if (products.data.length === 0) {
        return (
            <div className="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
                Tidak ada produk yang cocok. Coba ubah kata kunci atau filter.
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <p className="text-sm text-muted-foreground">{formatNumber(products.total)} produk</p>

            {/* Grid Produk dengan Styling ala Top Products */}
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                {products.data.map((product) => (
                    <Link
                        key={product.id}
                        href={product.href}
                        className="group flex h-full flex-col overflow-hidden rounded-[13px] border border-[#cfd4df] bg-white transition-transform duration-300 hover:-translate-y-1"
                    >
                        {/* Gambar Produk dengan Aspect Ratio 4:3 */}
                        <div className="aspect-[4/3] w-full shrink-0 overflow-hidden bg-[#F8F9FA]">
                            {product.image ? (
                                <img
                                    src={product.image}
                                    alt={product.name}
                                    loading="lazy"
                                    className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                    <ImageOff className="size-8" aria-hidden />
                                </div>
                            )}
                        </div>

                        {/* Konten Kartu (Judul, Harga Badge Hijau, Kategori/Store) */}
                        <div className="flex flex-1 flex-col p-4">
                            <div className="mb-2 flex items-start justify-between gap-2">
                                <h3 className="line-clamp-2 flex-1 font-bold text-[#0242C6]">
                                    {product.name}
                                </h3>
                                <span className="shrink-0 rounded-full bg-[#D3EDBC] px-2.5 py-1 text-[11px] font-bold text-[#012060]">
                                    {formatRupiah(product.price)}
                                </span>
                            </div>

                            {/* Informasi Tambahan (Kategori / Toko) */}
                            <div className="mt-auto pt-2 text-xs text-neutral-500 space-y-1">
                                <p className="truncate">{product.category.name}</p>
                                {product.storeName && <p className="truncate">{product.storeName}</p>}
                            </div>
                        </div>
                    </Link>
                ))}
            </div>

            {/* Paginasi */}
            {products.last_page > 1 && (
                <div className="flex items-center justify-center gap-3 pt-4 text-sm">
                    <Button asChild={!!products.prev_page_url} variant="outline" size="sm" disabled={!products.prev_page_url}>
                        {products.prev_page_url ? <Link href={products.prev_page_url}>Sebelumnya</Link> : <span>Sebelumnya</span>}
                    </Button>
                    <span className="tabular-nums text-muted-foreground">
                        {products.current_page} / {products.last_page}
                    </span>
                    <Button asChild={!!products.next_page_url} variant="outline" size="sm" disabled={!products.next_page_url}>
                        {products.next_page_url ? <Link href={products.next_page_url}>Berikutnya</Link> : <span>Berikutnya</span>}
                    </Button>
                </div>
            )}
        </div>
    );
}

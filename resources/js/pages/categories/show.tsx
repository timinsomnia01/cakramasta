import { Head, Link, router } from '@inertiajs/react';
import { CatalogToolbar, ProductGrid } from '@/components/store/catalog';
import { StoreLayout } from '@/components/store/store-layout';
import { ProductCard, type ProductCardData } from '@/components/store/product-card';
import { useFilters } from '@/components/marketplace/use-filters';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Star } from 'lucide-react';

type Props = {
    category: { name: string; slug: string; description: string | null; image: string | null };
    subCategories: { id: number; name: string; slug: string; productCount: number }[];
    activeSub: string | null;
    filters: { q: string; min_price: number | null; max_price: number | null; sort: string; rating?: string | number | null };
    sorts: string[];
    products: Paginated<ProductCardData>;
};

export default function CategoryShow({ category, subCategories, activeSub, filters, sorts, products }: Props) {
    const base = `/kategori/${category.slug}`;
    const filter = useFilters(base, { ...filters, sub: activeSub });

    // Fungsi bawaan untuk men-generate URL sub-kategori
    const subHref = (slug: string | null) => {
        const query = new URLSearchParams(Object.entries(filter.values).filter(([key, value]) => key !== 'sub' && value !== ''));
        if (slug) query.set('sub', slug);
        const qs = query.toString();
        return qs ? `${base}?${qs}` : base;
    };

    // Daftar rentang harga sesuai desain
    const priceRanges = [
        { label: '0 - 75RB', min: 0, max: 75000 },
        { label: '75RB - 150RB', min: 75000, max: 150000 },
        { label: '150RB - 200RB', min: 150000, max: 200000 },
        { label: 'More than 200RB', min: 200000, max: null },
    ];

    // ACTION: Handle klik pada tombol Price
    const handlePriceCheck = (min: number | null, max: number | null) => {
        const query = new URLSearchParams(window.location.search);

        // Cek apakah tombol yang diklik adalah tombol yang sedang aktif
        const currentMin = filters.min_price;
        const currentMax = filters.max_price;
        const isMatch = currentMin == min && currentMax == max;

        if (isMatch) {
            // Jika diklik lagi, hapus filternya (toggle off)
            query.delete('min_price');
            query.delete('max_price');
        } else {
            // Pasang filter baru
            if (min !== null) query.set('min_price', min.toString()); else query.delete('min_price');
            if (max !== null) query.set('max_price', max.toString()); else query.delete('max_price');
        }

        const qs = query.toString();
        const url = qs ? `${base}?${qs}` : base;
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    };

    // ACTION: Handle klik pada checkbox Category
    const handleCategoryCheck = (slug: string) => {
        const isActive = (activeSub ?? null) === slug;
        // Jika dicentang padahal sudah aktif, maka matikan (kirim null). Jika belum, aktifkan.
        const url = subHref(isActive ? null : slug);
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    };

    // ACTION: Handle klik pada checkbox Rating
    const handleRatingCheck = (rating: number) => {
        const query = new URLSearchParams(window.location.search);
        if (activeRating === rating.toString()) {
            query.delete('rating'); // Matikan centang
        } else {
            query.set('rating', rating.toString()); // Hidupkan centang
        }

        const qs = query.toString();
        const url = qs ? `${base}?${qs}` : base;
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    };

    // Cek status rating aktif di URL untuk UI checkbox
    const activeRating = filters.rating ? filters.rating.toString() : null;

    return (
        <StoreLayout>
            <Head title={category.name} />

            {/* --- HEADER: Breadcrumb --- */}
            <div className="mb-8">
                <nav className="mb-3 text-sm text-muted-foreground" aria-label="Breadcrumb">
                    <Link href="/" className="hover:text-[#012060]">Beranda</Link> / <Link href="/kategori" className="hover:text-[#012060]">Kategori</Link> / <span className="text-[#012060]">{category.name}</span>
                </nav>
                <h1 className="text-2xl text-[#012060] font-semibold">{category.name}</h1>
                {category.description && <p className="mt-1 max-w-2xl text-sm text-muted-foreground">{category.description}</p>}
            </div>

            {/* --- MAIN LAYOUT --- */}
            <div className="grid gap-8 md:grid-cols-[260px_1fr] lg:grid-cols-[280px_1fr]">

                {/* 1. SIDEBAR FILTER */}
                <aside className="min-w-0 h-fit rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                    <h2 className="mb-6 text-xl font-bold text-[#0ea5e9]">Filter by</h2>

                    {/* Filter Price (Berfungsi) */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-sm font-semibold text-[#0ea5e9]">Price</h3>
                        <div className="flex flex-col gap-2">
                            {priceRanges.map((range, idx) => {
                                // Cek apakah filter saat ini sama persis dengan rentang tombol ini
                                const isActive = filters.min_price == range.min && filters.max_price == range.max;

                                return (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => handlePriceCheck(range.min, range.max)}
                                        className={cn(
                                            "w-full rounded-md border px-3 py-2 text-left text-xs outline-none transition-colors",
                                            isActive
                                                ? "border-[#0ea5e9] bg-[#e0f2fe] font-bold text-[#0ea5e9]" // Gaya saat aktif
                                                : "border-gray-200 bg-white text-gray-500 hover:border-[#0ea5e9] hover:text-[#0ea5e9]" // Gaya bawaan
                                        )}
                                    >
                                        {range.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Filter Category (Berfungsi) */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-sm font-semibold text-[#0ea5e9]">Category</h3>
                        <div className="space-y-3">
                            {subCategories.map((sub) => {
                                const isActive = (activeSub ?? null) === sub.slug;
                                return (
                                    <label key={sub.id} className="flex cursor-pointer items-center gap-3 group">
                                        <input
                                            type="checkbox"
                                            checked={isActive}
                                            onChange={() => handleCategoryCheck(sub.slug)}
                                            className="size-4 cursor-pointer rounded border-gray-300 text-[#0ea5e9] focus:ring-[#0ea5e9]"
                                        />
                                        <span className={cn(
                                            "text-sm transition-colors group-hover:text-[#0ea5e9]",
                                            isActive ? "font-semibold text-[#012060]" : "text-gray-500"
                                        )}>
                                            {sub.name} {sub.productCount !== null && `(${sub.productCount})`}
                                        </span>
                                    </label>
                                );
                            })}
                        </div>
                    </div>

                    {/* Filter Rating (Berfungsi) */}
                    <div>
                        <h3 className="mb-3 text-sm font-semibold text-[#0ea5e9]">Rating</h3>
                        <div className="space-y-3">
                            {[5, 4, 3, 2, 1].map((rating) => {
                                const isActive = activeRating === rating.toString();
                                return (
                                    <label key={rating} className="flex cursor-pointer items-center gap-3 group">
                                        <input
                                            type="checkbox"
                                            checked={isActive}
                                            onChange={() => handleRatingCheck(rating)}
                                            className="size-4 cursor-pointer rounded border-gray-300 text-[#0ea5e9] focus:ring-[#0ea5e9]"
                                        />
                                        <div className="flex gap-1">
                                            {Array.from({ length: 5 }).map((_, i) => (
                                                <Star
                                                    key={i}
                                                    className={cn(
                                                        "size-4",
                                                        i < rating ? "fill-amber-400 text-amber-400" : "fill-gray-200 text-gray-200"
                                                    )}
                                                />
                                            ))}
                                        </div>
                                    </label>
                                );
                            })}
                        </div>
                    </div>
                </aside>

                {/* 2. AREA GRID PRODUK */}
                <div className="min-w-0 space-y-4">

                    {/* ================================================================
                        PENGATURAN UKURAN KARTU ADA DI SINI:
                        - grid-cols-2: Di HP tampil 2 kartu.
                        - lg:grid-cols-3: Di layar cukup besar (laptop) tampil 3 kartu.
                        - xl:grid-cols-4: Di layar sangat besar tampil 4 kartu.
                        Semakin banyak kolom, semakin KECIL ukuran kartunya.
                        Ubah angka ini sesuai dengan seberapa kecil kartu yang Anda mau.
                        ================================================================ */}
                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-2">
                        {products.data.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>

                    {/* Kode Lama (Dikommentari Sesuai Request) */}
                    {/*
                    <CatalogToolbar filter={{ ...filter, active: filter.values.q !== '' || filter.values.min_price !== '' || filter.values.max_price !== '' }} sorts={sorts} />
                    <ProductGrid products={products} />
                    */}
                </div>
            </div>
        </StoreLayout>
    );
}

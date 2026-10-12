import { Head, Link } from '@inertiajs/react';
import { useEffect } from 'react';
import BannerCarousel from '@/components/marketplace/banner-carousel';
import Navbar from '@/components/marketplace/navbar';
import type { Category, ProductCard } from '@/types/marketplace';

type Props = {
    dailyProducts: ProductCard[];
    categories: Category[];
    newProducts: ProductCard[];
};

export default function Home({
    dailyProducts = [],
    categories = [],
    newProducts = [],
}: Partial<Props>) {
    // Paksa mode terang (light mode) untuk halaman beranda publik
    useEffect(() => {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
    }, []);

    return (
        <div className="min-h-screen bg-[#F8F9FA] text-neutral-900">
            <Head title="Beranda - Cakramasta" />

            {/* Navbar Cakramasta */}
            <Navbar />

            {/* Carousel Banner Promosi */}
            <BannerCarousel />

            <main className="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                {/* Kategori */}
                {categories.length > 0 && (
                    <section className="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-xs">
                        <h2 className="mb-3 text-lg font-semibold text-neutral-900 sm:text-xl">
                            Kategori Pilihan
                        </h2>
                        <div className="flex flex-wrap gap-2">
                            {categories.map((category) => (
                                <Link
                                    key={category.id}
                                    href={category.href}
                                    className="rounded-full border border-neutral-200 bg-neutral-50 px-3.5 py-1.5 text-sm font-medium text-neutral-700 transition-colors hover:border-neutral-300 hover:bg-neutral-100"
                                >
                                    {category.name}{' '}
                                    <span className="text-xs text-neutral-500">
                                        ({category.productCount})
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                {/* Produk Pilihan Hari Ini */}
                <section className="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-xs">
                    <h2 className="mb-2 text-lg font-semibold text-neutral-900 sm:text-xl">
                        Produk Pilihan Hari Ini
                    </h2>
                    <p className="text-sm text-neutral-500">
                        {dailyProducts.length} produk pilihan tersedia.
                    </p>
                </section>

                {/* Produk Terbaru */}
                {newProducts.length > 0 && (
                    <section className="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-xs">
                        <h2 className="mb-2 text-lg font-semibold text-neutral-900 sm:text-xl">
                            Produk Terbaru
                        </h2>
                        <p className="text-sm text-neutral-500">
                            {newProducts.length} produk baru ditambahkan.
                        </p>
                    </section>
                )}
            </main>
        </div>
    );
}

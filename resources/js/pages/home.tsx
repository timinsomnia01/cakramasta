import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useRef } from 'react';
import BannerCarousel from '@/components/marketplace/banner-carousel';
import TopProducts, { type TopProductData } from '@/components/marketplace/top-products';
import { CategoriesSection } from '@/components/store/categories';
import { StoreLayout } from '@/components/store/store-layout';
import { ProductCard, type ProductCardData } from '@/components/store/product-card';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import LabelSection from '@/components/marketplace/label-section';

type CategoryItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    productCount: number;
    href: string;
};

type Props = {
    dailyProducts: ProductCardData[];
    categories: CategoryItem[];
    newProducts: ProductCardData[];
    topProducts: TopProductData[];
};

function Section({ title, action, children }: { title: string; action?: React.ReactNode; children: React.ReactNode }) {
    return (
        <section className="space-y-3">
            <div className="flex items-end justify-between gap-3">
                <h2 className="text-lg font-semibold">{title}</h2>
                {action}
            </div>
            {children}
        </section>
    );
}

export default function Home({ dailyProducts, categories, newProducts, topProducts }: Props) {
    const track = useRef<HTMLDivElement>(null);
    const scroll = (direction: 1 | -1) =>
        track.current?.scrollBy({ left: direction * track.current.clientWidth * 0.8, behavior: 'smooth' });

    // Paksa mode terang untuk halaman publik
    useEffect(() => {
        document.documentElement.classList.remove('dark');
    }, []);

    return (
        <StoreLayout>
            <Head title="Beranda" />
            {/* Banner carousel – ditampilkan sebelum konten utama */}
            <div className="-mx-4 -mt-6 sm:-mx-4">
                <BannerCarousel />
            </div>
            <div className="mb-10">
                <TopProducts products={topProducts} />
            </div>
            <CategoriesSection categories={categories} />
            {/* <div className="space-y-10">
                {dailyProducts.length > 0 && (
                    <Section
                        title="Pilihan hari ini"
                        action={
                            <div className="flex gap-1">
                                <Button variant="outline" size="icon" className="size-8" onClick={() => scroll(-1)} aria-label="Sebelumnya">
                                    <ChevronLeft className="size-4" />
                                </Button>
                                <Button variant="outline" size="icon" className="size-8" onClick={() => scroll(1)} aria-label="Berikutnya">
                                    <ChevronRight className="size-4" />
                                </Button>
                            </div>
                        }
                    >
                        <div
                            ref={track}
                            className="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                        >
                            {dailyProducts.map((product) => (
                                <div key={product.id} className="w-40 shrink-0 snap-start sm:w-52">
                                    <ProductCard product={product} />
                                </div>
                            ))}
                        </div>
                    </Section>
                )}

                <Section title="Kategori" action={<Link href="/kategori" className="text-sm text-muted-foreground hover:text-foreground">Lihat semua</Link>}>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        {categories.map((category) => (
                            <Link
                                key={category.id}
                                href={category.href}
                                className="group min-w-0 overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md"
                            >
                                <div className="aspect-[16/9] bg-muted">
                                    {category.image && (
                                        <img src={category.image} alt="" loading="lazy" className="size-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                    )}
                                </div>
                                <div className="p-3">
                                    <p className="truncate text-sm font-medium">{category.name}</p>
                                    <p className="text-xs text-muted-foreground">{formatNumber(category.productCount)} produk</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </Section>

                <Section title="Produk terbaru">
                    {newProducts.length === 0 ? (
                        <p className="text-sm text-muted-foreground">Belum ada produk.</p>
                    ) : (
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            {newProducts.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    )}
                </Section>
            </div> */}
        </StoreLayout>
    );
}


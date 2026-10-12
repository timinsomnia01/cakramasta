import { Head, Link } from '@inertiajs/react';
import { CatalogToolbar, ProductGrid } from '@/components/store/catalog';
import { StoreLayout } from '@/components/store/store-layout';
import type { ProductCardData } from '@/components/store/product-card';
import { useFilters } from '@/components/marketplace/use-filters';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

type Props = {
    category: { name: string; slug: string; description: string | null; image: string | null };
    subCategories: { id: number; name: string; slug: string; productCount: number }[];
    activeSub: string | null;
    filters: { q: string; min_price: number | null; max_price: number | null; sort: string };
    sorts: string[];
    products: Paginated<ProductCardData>;
};

export default function CategoryShow({ category, subCategories, activeSub, filters, sorts, products }: Props) {
    const base = `/kategori/${category.slug}`;
    const filter = useFilters(base, { ...filters, sub: activeSub });
    const subHref = (slug: string | null) => {
        const query = new URLSearchParams(Object.entries(filter.values).filter(([key, value]) => key !== 'sub' && value !== ''));
        if (slug) query.set('sub', slug);
        const qs = query.toString();
        return qs ? `${base}?${qs}` : base;
    };

    return (
        <StoreLayout>
            <Head title={category.name} />
            <nav className="mb-3 text-sm text-muted-foreground" aria-label="Breadcrumb">
                <Link href="/" className="hover:text-foreground">Beranda</Link> / <Link href="/kategori" className="hover:text-foreground">Kategori</Link> / <span className="text-foreground">{category.name}</span>
            </nav>
            <h1 className="text-2xl font-semibold">{category.name}</h1>
            {category.description && <p className="mt-1 mb-6 max-w-2xl text-sm text-muted-foreground">{category.description}</p>}

            <div className="grid gap-6 md:grid-cols-[14rem_1fr]">
                <aside className="min-w-0">
                    <h2 className="mb-2 text-sm font-medium">Sub kategori</h2>
                    <ul className="flex gap-2 overflow-x-auto pb-1 md:flex-col md:gap-1 md:overflow-visible">
                        {[{ id: 0, name: 'Semua', slug: null as string | null, productCount: null as number | null }, ...subCategories].map((sub) => (
                            <li key={sub.id} className="shrink-0">
                                <Link
                                    href={subHref(sub.slug)}
                                    className={cn(
                                        'flex items-center justify-between gap-3 rounded-md border px-3 py-1.5 text-sm md:border-0',
                                        (activeSub ?? null) === sub.slug ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                                    )}
                                >
                                    <span className="whitespace-nowrap">{sub.name}</span>
                                    {sub.productCount !== null && <span className="text-xs opacity-70">{sub.productCount}</span>}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </aside>
                <div className="min-w-0 space-y-4">
                    <CatalogToolbar filter={{ ...filter, active: filter.values.q !== '' || filter.values.min_price !== '' || filter.values.max_price !== '' }} sorts={sorts} />
                    <ProductGrid products={products} />
                </div>
            </div>
        </StoreLayout>
    );
}

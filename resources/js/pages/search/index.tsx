import { Head } from '@inertiajs/react';
import { CatalogToolbar, ProductGrid } from '@/components/store/catalog';
import { StoreLayout } from '@/components/store/store-layout';
import type { ProductCardData } from '@/components/store/product-card';
import { NativeSelect } from '@/components/marketplace/native-select';
import { useFilters } from '@/components/marketplace/use-filters';
import type { Paginated } from '@/types';

type Props = {
    filters: { q: string; kategori: string | null; min_price: number | null; max_price: number | null; sort: string };
    sorts: string[];
    categories: { id: number; name: string; slug: string }[];
    products: Paginated<ProductCardData>;
};

export default function SearchIndex({ filters, sorts, categories, products }: Props) {
    const filter = useFilters('/cari', filters);

    return (
        <StoreLayout initialQuery={filters.q}>
            <Head title={filters.q ? `Cari "${filters.q}"` : 'Cari produk'} />
            <h1 className="mb-4 text-2xl font-semibold">{filters.q ? <>Hasil untuk &ldquo;{filters.q}&rdquo;</> : 'Semua produk'}</h1>
            <div className="space-y-4">
                <CatalogToolbar filter={filter} sorts={sorts}>
                    <NativeSelect
                        value={filter.values.kategori ?? ''}
                        onChange={(event) => filter.set('kategori', event.target.value)}
                        aria-label="Kategori"
                        className="w-auto"
                    >
                        <option value="">Semua kategori</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.slug}>{category.name}</option>
                        ))}
                    </NativeSelect>
                </CatalogToolbar>
                <ProductGrid products={products} />
            </div>
        </StoreLayout>
    );
}

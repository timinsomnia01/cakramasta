import { Head, Link } from '@inertiajs/react';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductThumb } from '@/components/marketplace/product-thumb';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, Paginated, ProductRow } from '@/types';

type Props = { products: Paginated<ProductRow>; filters: { status: string | null; q: string }; statuses: Option[] };

export default function VerificationProducts({ products, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.verification.products, initial);

    const columns: Column<ProductRow>[] = [
        { header: 'Produk', cell: (p) => (
            <div className="flex items-center gap-3">
                <ProductThumb src={p.image} alt={p.name} />
                <div className="min-w-0">
                    <Link href={`${urls.verification.products}/${p.id}`} className="line-clamp-1 font-medium underline-offset-4 hover:underline">{p.name}</Link>
                    <p className="text-xs text-muted-foreground">{p.category}</p>
                </div>
            </div>
        ) },
        { header: 'Toko', cell: (p) => p.storeName },
        { header: 'Harga', align: 'right', cell: (p) => formatRupiah(p.price) },
        { header: 'Status', cell: (p) => <StatusBadge status={p.status} label={p.statusLabel} /> },
        { header: '', cell: (p) => <Button asChild size="sm" variant={p.status === 'pending' ? 'default' : 'ghost'}><Link href={`${urls.verification.products}/${p.id}`}>{p.status === 'pending' ? 'Tinjau' : 'Detail'}</Link></Button> },
    ];

    return (
        <>
            <Head title="Verifikasi Produk" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Verifikasi produk" description="Produk baru atau yang isinya diubah penjual." />
                <DataTable columns={columns} page={products} rowKey={(p) => p.id} empty="Tidak ada produk." toolbar={<FilterBar filters={filters} placeholder="Cari produk atau toko" statuses={statuses} />} />
            </div>
        </>
    );
}

VerificationProducts.layout = crumbs(['Verifikasi', urls.verification.dashboard], ['Produk', urls.verification.products]);

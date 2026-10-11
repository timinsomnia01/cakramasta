import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { ConfirmAction } from '@/components/marketplace/action-dialogs';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { NativeSelect } from '@/components/marketplace/native-select';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductThumb } from '@/components/marketplace/product-thumb';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { crumbs } from '@/lib/crumbs';
import { formatNumber, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, Paginated, ProductRow } from '@/types';

type Props = {
    products: Paginated<ProductRow>;
    filters: { q: string; status: string | null; category: number | null; seller: number | null; min: number | null; max: number | null };
    statuses: Option[];
    categories: { id: number; name: string; depth: number }[];
    sellers: { id: number; name: string }[];
};

export default function AdminProducts({ products, filters: initial, statuses, categories, sellers }: Props) {
    const filters = useFilters(urls.admin.products, initial);

    const columns: Column<ProductRow>[] = [
        { header: 'Produk', cell: (p) => (
            <div className="flex items-center gap-3">
                <ProductThumb src={p.image} alt={p.name} />
                <div className="min-w-0">
                    <Link href={`${urls.admin.products}/${p.id}/ubah`} className="line-clamp-1 font-medium underline-offset-4 hover:underline">{p.name}</Link>
                    <p className="text-xs text-muted-foreground">{p.category}{p.sku && ` · ${p.sku}`}</p>
                </div>
            </div>
        ) },
        { header: 'Toko', cell: (p) => p.storeName },
        { header: 'Harga', align: 'right', cell: (p) => formatRupiah(p.price) },
        { header: 'Stok', align: 'right', cell: (p) => formatNumber(p.stock) },
        { header: 'Terjual', align: 'right', cell: (p) => formatNumber(p.soldCount) },
        { header: 'Status', cell: (p) => <StatusBadge status={p.status} label={p.statusLabel} /> },
        { header: '', cell: (p) => (
            <div className="flex justify-end gap-1">
                <Button asChild size="icon" variant="ghost" aria-label={`Ubah ${p.name}`}><Link href={`${urls.admin.products}/${p.id}/ubah`}><Pencil aria-hidden /></Link></Button>
                <ConfirmAction
                    trigger={<Button size="sm" variant="ghost" className="text-red-700 dark:text-red-400">Hapus</Button>}
                    title={`Hapus "${p.name}"?`}
                    description="Produk hilang dari katalog. Riwayat pesanan lama tidak terpengaruh."
                    confirmLabel="Hapus produk"
                    url={`${urls.admin.products}/${p.id}`}
                    method="delete"
                    destructive
                />
            </div>
        ) },
    ];

    return (
        <>
            <Head title="Produk" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Produk" description="Semua produk dari seluruh toko." actions={<Button asChild size="sm"><Link href={`${urls.admin.products}/buat`}><Plus aria-hidden /> Tambah produk</Link></Button>} />
                <DataTable
                    columns={columns}
                    page={products}
                    rowKey={(p) => p.id}
                    empty="Tidak ada produk yang cocok."
                    toolbar={
                        <FilterBar filters={filters} placeholder="Cari nama, SKU, atau toko" statuses={statuses}>
                            <NativeSelect aria-label="Kategori" value={filters.values.category} onChange={(e) => filters.set('category', e.target.value)} className="w-auto min-w-40">
                                <option value="">Semua kategori</option>
                                {categories.map((c) => <option key={c.id} value={c.id}>{c.depth ? '— ' : ''}{c.name}</option>)}
                            </NativeSelect>
                            <NativeSelect aria-label="Toko" value={filters.values.seller} onChange={(e) => filters.set('seller', e.target.value)} className="w-auto min-w-40">
                                <option value="">Semua toko</option>
                                {sellers.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                            </NativeSelect>
                            <Input type="number" inputMode="numeric" min={0} placeholder="Harga min" aria-label="Harga minimum" value={filters.values.min} onChange={(e) => filters.set('min', e.target.value, true)} className="w-28" />
                            <Input type="number" inputMode="numeric" min={0} placeholder="Harga maks" aria-label="Harga maksimum" value={filters.values.max} onChange={(e) => filters.set('max', e.target.value, true)} className="w-28" />
                        </FilterBar>
                    }
                />
            </div>
        </>
    );
}

AdminProducts.layout = crumbs(['Admin', urls.admin.dashboard], ['Produk', urls.admin.products]);

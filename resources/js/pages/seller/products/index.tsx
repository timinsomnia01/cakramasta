import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { ConfirmAction } from '@/components/marketplace/action-dialogs';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductThumb } from '@/components/marketplace/product-thumb';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { postAction } from '@/lib/actions';
import { crumbs } from '@/lib/crumbs';
import { formatNumber, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, Paginated, ProductRow } from '@/types';

type Props = {
    products: Paginated<ProductRow>;
    filters: { status: string | null; q: string };
    statuses: Option[];
};

export default function SellerProducts({ products, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.seller.products, initial);

    const columns: Column<ProductRow>[] = [
        {
            header: 'Produk',
            cell: (p) => (
                <div className="flex items-center gap-3">
                    <ProductThumb src={p.image} alt={p.name} />
                    <div className="min-w-0">
                        <Link href={`${urls.seller.products}/${p.id}/ubah`} className="line-clamp-1 font-medium underline-offset-4 hover:underline">{p.name}</Link>
                        <p className="text-xs text-muted-foreground">{p.category}{p.sku && ` · ${p.sku}`}</p>
                        {p.status === 'rejected' && p.rejectionReason && <p className="max-w-72 truncate text-xs text-red-700 dark:text-red-400">Ditolak: {p.rejectionReason}</p>}
                    </div>
                </div>
            ),
        },
        { header: 'Harga', align: 'right', cell: (p) => formatRupiah(p.price) },
        { header: 'Stok', align: 'right', cell: (p) => <span className={p.stock <= 3 ? 'font-medium text-amber-700 dark:text-amber-400' : ''}>{formatNumber(p.stock)}</span> },
        { header: 'Terjual', align: 'right', cell: (p) => formatNumber(p.soldCount) },
        { header: 'Status', cell: (p) => <StatusBadge status={p.status} label={p.statusLabel} /> },
        {
            header: '',
            cell: (p) => (
                <div className="flex justify-end gap-1">
                    {(p.status === 'draft' || p.status === 'rejected') && (
                        <Button size="sm" variant="outline" onClick={() => postAction(`${urls.seller.products}/${p.id}/ajukan`)}>Ajukan</Button>
                    )}
                    {(p.status === 'approved' || p.status === 'inactive') && (
                        <Button size="sm" variant="outline" onClick={() => postAction(`${urls.seller.products}/${p.id}/aktif`)}>{p.status === 'approved' ? 'Nonaktifkan' : 'Aktifkan'}</Button>
                    )}
                    <Button asChild size="icon" variant="ghost" aria-label={`Ubah ${p.name}`}>
                        <Link href={`${urls.seller.products}/${p.id}/ubah`}><Pencil aria-hidden /></Link>
                    </Button>
                    <ConfirmAction
                        trigger={<Button size="sm" variant="ghost" className="text-red-700 dark:text-red-400">Hapus</Button>}
                        title={`Hapus "${p.name}"?`}
                        description="Produk hilang dari katalog. Riwayat pesanan lama tidak terpengaruh."
                        confirmLabel="Hapus produk"
                        url={`${urls.seller.products}/${p.id}`}
                        method="delete"
                        destructive
                    />
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Produk Saya" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Produk saya"
                    actions={<Button asChild size="sm"><Link href={urls.seller.newProduct}><Plus aria-hidden /> Tambah produk</Link></Button>}
                />
                <DataTable
                    columns={columns}
                    page={products}
                    rowKey={(p) => p.id}
                    empty="Belum ada produk. Tambahkan produk pertamamu!"
                    toolbar={<FilterBar filters={filters} placeholder="Cari nama atau SKU" statuses={statuses} />}
                />
            </div>
        </>
    );
}

SellerProducts.layout = crumbs(['Toko', urls.seller.dashboard], ['Produk', urls.seller.products]);

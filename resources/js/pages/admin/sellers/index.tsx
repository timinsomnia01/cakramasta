import { Head, Link } from '@inertiajs/react';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatNumber } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, Paginated, SellerRow } from '@/types';

type Props = { sellers: Paginated<SellerRow>; filters: { status: string | null; q: string }; statuses: Option[] };

export default function AdminSellers({ sellers, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.admin.sellers, initial);

    const columns: Column<SellerRow>[] = [
        { header: 'Toko', cell: (s) => <Link href={`${urls.admin.sellers}/${s.id}`} className="font-medium underline-offset-4 hover:underline">{s.storeName}</Link> },
        { header: 'Pemilik', cell: (s) => (<>{s.ownerName}<p className="text-xs text-muted-foreground">{s.ownerEmail}</p></>) },
        { header: 'Produk', align: 'right', cell: (s) => formatNumber(s.productsCount ?? 0) },
        { header: 'Pesanan', align: 'right', cell: (s) => formatNumber(s.ordersCount ?? 0) },
        { header: 'Mendaftar', cell: (s) => <span className="text-muted-foreground">{formatDateTime(s.createdAt)}</span> },
        { header: 'Status', cell: (s) => <StatusBadge status={s.status} label={s.statusLabel} /> },
        { header: '', cell: (s) => <Button asChild size="sm" variant="ghost"><Link href={`${urls.admin.sellers}/${s.id}`}>Detail</Link></Button> },
    ];

    return (
        <>
            <Head title="Penjual" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Penjual" description="Kelola toko: nonaktifkan atau aktifkan kembali." />
                <DataTable columns={columns} page={sellers} rowKey={(s) => s.id} empty="Tidak ada penjual." toolbar={<FilterBar filters={filters} placeholder="Cari toko, nama, atau email" statuses={statuses} />} />
            </div>
        </>
    );
}

AdminSellers.layout = crumbs(['Admin', urls.admin.dashboard], ['Penjual', urls.admin.sellers]);

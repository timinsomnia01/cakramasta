import { Head, Link } from '@inertiajs/react';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, OrderRow, Paginated } from '@/types';

type Props = {
    orders: Paginated<OrderRow>;
    filters: { status: string | null; q: string };
    statuses: Option[];
};

export default function OrdersIndex({ orders, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.account.orders, initial);

    const columns: Column<OrderRow>[] = [
        {
            header: 'Pesanan',
            cell: (o) => (
                <>
                    <Link href={o.href} className="font-medium underline-offset-4 hover:underline">{o.code}</Link>
                    <p className="max-w-72 truncate text-xs text-muted-foreground">
                        {o.items[0]?.name}{o.itemsCount > 1 && ` +${o.itemsCount - 1} lainnya`}
                    </p>
                </>
            ),
        },
        { header: 'Toko', cell: (o) => o.storeName },
        { header: 'Tanggal', cell: (o) => <span className="text-muted-foreground">{formatDateTime(o.createdAt)}</span> },
        { header: 'Status', cell: (o) => <StatusBadge status={o.status} label={o.statusLabel} /> },
        { header: 'Total', align: 'right', cell: (o) => formatRupiah(o.total) },
        { header: '', cell: (o) => <Button asChild size="sm" variant="ghost"><Link href={o.href}>Detail</Link></Button> },
    ];

    return (
        <>
            <Head title="Riwayat Pesanan" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Riwayat pesanan" description="Semua pesanan yang pernah kamu buat." />
                <DataTable
                    columns={columns}
                    page={orders}
                    rowKey={(o) => o.code}
                    empty="Tidak ada pesanan yang cocok."
                    toolbar={<FilterBar filters={filters} placeholder="Cari kode atau nama produk" statuses={statuses} />}
                />
            </div>
        </>
    );
}

OrdersIndex.layout = crumbs(['Akun saya', urls.account.dashboard], ['Pesanan', urls.account.orders]);

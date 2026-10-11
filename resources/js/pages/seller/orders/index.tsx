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

type Props = { orders: Paginated<OrderRow>; filters: { status: string | null; q: string }; statuses: Option[] };

export default function SellerOrders({ orders, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.seller.orders, initial);

    const columns: Column<OrderRow>[] = [
        {
            header: 'Pesanan',
            cell: (o) => (
                <>
                    <Link href={o.href} className="font-medium underline-offset-4 hover:underline">{o.code}</Link>
                    <p className="max-w-72 truncate text-xs text-muted-foreground">{o.items[0]?.name}{o.itemsCount > 1 && ` +${o.itemsCount - 1} lainnya`}</p>
                </>
            ),
        },
        { header: 'Pembeli', cell: (o) => o.buyerName },
        { header: 'Tanggal', cell: (o) => <span className="text-muted-foreground">{formatDateTime(o.createdAt)}</span> },
        { header: 'Status', cell: (o) => <StatusBadge status={o.status} label={o.statusLabel} /> },
        { header: 'Pendapatan', align: 'right', cell: (o) => formatRupiah(o.sellerIncome) },
        { header: '', cell: (o) => <Button asChild size="sm" variant={o.status === 'paid' || o.status === 'processing' ? 'default' : 'ghost'}><Link href={o.href}>{o.status === 'paid' ? 'Proses' : o.status === 'processing' ? 'Kirim' : 'Detail'}</Link></Button> },
    ];

    return (
        <>
            <Head title="Pesanan Masuk" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Pesanan masuk" description="Pesanan yang belum dibayar pembeli belum ditampilkan." />
                <DataTable columns={columns} page={orders} rowKey={(o) => o.code} empty="Belum ada pesanan." toolbar={<FilterBar filters={filters} placeholder="Cari kode atau nama penerima" statuses={statuses} />} />
            </div>
        </>
    );
}

SellerOrders.layout = crumbs(['Toko', urls.seller.dashboard], ['Pesanan', urls.seller.orders]);

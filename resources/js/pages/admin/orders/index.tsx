import { Head, Link } from '@inertiajs/react';
import { Download } from 'lucide-react';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { NativeSelect } from '@/components/marketplace/native-select';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Option, OrderRow, Paginated } from '@/types';

type Props = {
    orders: Paginated<OrderRow>;
    filters: { search: string; status: string | null; seller_id: number | null; from: string | null; to: string | null };
    statuses: Option[];
    sellers: { id: number; name: string }[];
};

export default function AdminOrders({ orders, filters: initial, statuses, sellers }: Props) {
    const filters = useFilters(urls.admin.orders, initial);

    // Ekspor mengikuti filter yang sedang aktif di layar.
    const exportQuery = new URLSearchParams(Object.entries(filters.values).filter(([, v]) => v !== '')).toString();

    const columns: Column<OrderRow>[] = [
        { header: 'Pesanan', cell: (o) => (<><Link href={o.href} className="font-medium underline-offset-4 hover:underline">{o.code}</Link><p className="max-w-64 truncate text-xs text-muted-foreground">{o.items[0]?.name}{o.itemsCount > 1 && ` +${o.itemsCount - 1} lainnya`}</p></>) },
        { header: 'Pembeli', cell: (o) => o.buyerName },
        { header: 'Toko', cell: (o) => o.storeName },
        { header: 'Tanggal', cell: (o) => <span className="text-muted-foreground">{formatDateTime(o.createdAt)}</span> },
        { header: 'Status', cell: (o) => <StatusBadge status={o.status} label={o.statusLabel} /> },
        { header: 'Total', align: 'right', cell: (o) => formatRupiah(o.total) },
    ];

    return (
        <>
            <Head title="Log Pembelian" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Log pembelian"
                    description="Seluruh transaksi di UM-Mart."
                    actions={<Button asChild variant="outline" size="sm"><a href={`${urls.admin.orders}/export${exportQuery ? `?${exportQuery}` : ''}`}><Download aria-hidden /> Ekspor Excel</a></Button>}
                />
                <DataTable
                    columns={columns}
                    page={orders}
                    rowKey={(o) => o.code}
                    empty="Tidak ada pesanan yang cocok."
                    toolbar={
                        <FilterBar filters={filters} searchKey="search" placeholder="Cari kode, penerima, atau pembeli" statuses={statuses}>
                            <NativeSelect aria-label="Toko" value={filters.values.seller_id} onChange={(e) => filters.set('seller_id', e.target.value)} className="w-auto min-w-40">
                                <option value="">Semua toko</option>
                                {sellers.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                            </NativeSelect>
                            <Input type="date" aria-label="Dari tanggal" value={filters.values.from} onChange={(e) => filters.set('from', e.target.value)} className="w-auto" />
                            <Input type="date" aria-label="Sampai tanggal" value={filters.values.to} onChange={(e) => filters.set('to', e.target.value)} className="w-auto" />
                        </FilterBar>
                    }
                />
            </div>
        </>
    );
}

AdminOrders.layout = crumbs(['Admin', urls.admin.dashboard], ['Pembelian', urls.admin.orders]);

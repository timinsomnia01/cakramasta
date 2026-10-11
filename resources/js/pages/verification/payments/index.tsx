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
import type { Option, Paginated, Payment } from '@/types';

type Row = Payment & { orderCode: string; buyerName: string | null; paidAt: string | null };
type Props = { payments: Paginated<Row>; filters: { status: string | null; q: string }; statuses: Option[] };

export default function VerificationPayments({ payments, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.verification.payments, initial);

    const columns: Column<Row>[] = [
        { header: 'Pesanan', cell: (p) => <Link href={`${urls.verification.payments}/${p.id}`} className="font-medium underline-offset-4 hover:underline">{p.orderCode}</Link> },
        { header: 'Pembeli', cell: (p) => p.buyerName },
        { header: 'Metode', cell: (p) => p.methodLabel },
        { header: 'Diunggah', cell: (p) => <span className="text-muted-foreground">{p.paidAt ? formatDateTime(p.paidAt) : '—'}</span> },
        { header: 'Status', cell: (p) => <StatusBadge status={p.status} label={p.statusLabel} /> },
        { header: 'Nominal', align: 'right', cell: (p) => formatRupiah(p.amount) },
        { header: '', cell: (p) => <Button asChild size="sm" variant={p.status === 'waiting_verification' ? 'default' : 'ghost'}><Link href={`${urls.verification.payments}/${p.id}`}>{p.status === 'waiting_verification' ? 'Periksa' : 'Detail'}</Link></Button> },
    ];

    return (
        <>
            <Head title="Verifikasi Pembayaran" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Verifikasi pembayaran" description="Cocokkan bukti transfer dengan nominal pesanan." />
                <DataTable columns={columns} page={payments} rowKey={(p) => p.id} empty="Tidak ada pembayaran." toolbar={<FilterBar filters={filters} placeholder="Cari kode pesanan atau pembeli" statuses={statuses} />} />
            </div>
        </>
    );
}

VerificationPayments.layout = crumbs(['Verifikasi', urls.verification.dashboard], ['Pembayaran', urls.verification.payments]);

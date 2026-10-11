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
import type { Option, Paginated, WithdrawalRow } from '@/types';

type Props = { withdrawals: Paginated<WithdrawalRow>; filters: { status: string | null; q: string }; statuses: Option[] };

export default function VerificationWithdrawals({ withdrawals, filters: initial, statuses }: Props) {
    const filters = useFilters(urls.verification.withdrawals, initial);

    const columns: Column<WithdrawalRow>[] = [
        { header: 'Kode', cell: (w) => <Link href={`${urls.verification.withdrawals}/${w.code}`} className="font-mono text-xs font-medium underline-offset-4 hover:underline">{w.code}</Link> },
        { header: 'Toko', cell: (w) => w.storeName },
        { header: 'Tujuan', cell: (w) => <span className="block max-w-56 truncate">{w.destination}</span> },
        { header: 'Diajukan', cell: (w) => <span className="text-muted-foreground">{formatDateTime(w.createdAt)}</span> },
        { header: 'Status', cell: (w) => <StatusBadge status={w.status} label={w.statusLabel} /> },
        { header: 'Nominal', align: 'right', cell: (w) => formatRupiah(w.amount) },
        { header: '', cell: (w) => <Button asChild size="sm" variant={w.status === 'pending' || w.status === 'approved' ? 'default' : 'ghost'}><Link href={`${urls.verification.withdrawals}/${w.code}`}>{w.status === 'pending' ? 'Tinjau' : w.status === 'approved' ? 'Transfer' : 'Detail'}</Link></Button> },
    ];

    return (
        <>
            <Head title="Verifikasi Pencairan" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Verifikasi pencairan" description="Setujui, transfer, lalu unggah bukti transfer." />
                <DataTable columns={columns} page={withdrawals} rowKey={(w) => w.code} empty="Tidak ada pencairan." toolbar={<FilterBar filters={filters} placeholder="Cari kode atau toko" statuses={statuses} />} />
            </div>
        </>
    );
}

VerificationWithdrawals.layout = crumbs(['Verifikasi', urls.verification.dashboard], ['Pencairan', urls.verification.withdrawals]);

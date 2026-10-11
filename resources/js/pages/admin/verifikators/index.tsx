import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { ConfirmAction } from '@/components/marketplace/action-dialogs';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FilterBar } from '@/components/marketplace/filter-bar';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { useFilters } from '@/components/marketplace/use-filters';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Paginated } from '@/types';

type Row = { id: number; name: string; email: string; phone: string | null; isActive: boolean; createdAt: string };

export default function Verifikators({ verifikators, filters: initial }: { verifikators: Paginated<Row>; filters: { q: string } }) {
    const filters = useFilters(urls.admin.verifikators, initial);

    const columns: Column<Row>[] = [
        { header: 'Nama', cell: (v) => (<><span className="font-medium">{v.name}</span><p className="text-xs text-muted-foreground">{v.email}</p></>) },
        { header: 'Nomor HP', cell: (v) => v.phone ?? '—' },
        { header: 'Dibuat', cell: (v) => <span className="text-muted-foreground">{formatDateTime(v.createdAt)}</span> },
        { header: 'Status', cell: (v) => <StatusBadge status={v.isActive ? 'approved' : 'suspended'} label={v.isActive ? 'Aktif' : 'Nonaktif'} /> },
        { header: '', cell: (v) => (
            <div className="flex justify-end gap-1">
                <Button asChild size="icon" variant="ghost" aria-label={`Ubah ${v.name}`}><Link href={`${urls.admin.verifikators}/${v.id}/edit`}><Pencil aria-hidden /></Link></Button>
                <ConfirmAction
                    trigger={<Button size="sm" variant="ghost" className="text-red-700 dark:text-red-400">Hapus</Button>}
                    title={`Hapus ${v.name}?`}
                    description="Akun dihapus permanen. Riwayat verifikasi yang pernah dilakukan tetap tersimpan."
                    confirmLabel="Hapus akun"
                    url={`${urls.admin.verifikators}/${v.id}`}
                    method="delete"
                    destructive
                />
            </div>
        ) },
    ];

    return (
        <>
            <Head title="Verifikator" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Verifikator" description="Akun tim yang memeriksa penjual, produk, pembayaran, dan pencairan." actions={<Button asChild size="sm"><Link href={`${urls.admin.verifikators}/create`}><Plus aria-hidden /> Tambah verifikator</Link></Button>} />
                <DataTable columns={columns} page={verifikators} rowKey={(v) => v.id} empty="Belum ada verifikator." toolbar={<FilterBar filters={filters} placeholder="Cari nama atau email" />} />
            </div>
        </>
    );
}

Verifikators.layout = crumbs(['Admin', urls.admin.dashboard], ['Verifikator', urls.admin.verifikators]);

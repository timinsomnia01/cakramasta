import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { DataTable } from '@/components/marketplace/data-table';
import type { Column } from '@/components/marketplace/data-table';
import { FormField } from '@/components/marketplace/form-field';
import { NativeSelect } from '@/components/marketplace/native-select';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Paginated, WithdrawalRow } from '@/types';

type Props = {
    balance: number;
    totalIncome: number;
    minimum: number;
    destinations: { qris: boolean; bank: { bank: string; number: string; holder: string } | null };
    withdrawals: Paginated<WithdrawalRow>;
};

export default function SellerWithdrawals({ balance, totalIncome, minimum, destinations, withdrawals }: Props) {
    const form = useForm({ amount: '', method: destinations.qris ? 'qris' : 'bank_transfer', notes: '' });
    const canWithdraw = balance >= minimum;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.seller.withdrawals, { preserveScroll: true, onSuccess: () => form.reset('amount', 'notes') });
    };

    const columns: Column<WithdrawalRow>[] = [
        { header: 'Kode', cell: (w) => <span className="font-mono text-xs">{w.code}</span> },
        { header: 'Tanggal', cell: (w) => <span className="text-muted-foreground">{formatDateTime(w.createdAt)}</span> },
        { header: 'Tujuan', cell: (w) => w.destination },
        { header: 'Status', cell: (w) => (
            <div>
                <StatusBadge status={w.status} label={w.statusLabel} />
                {w.rejectionReason && <p className="mt-1 max-w-56 text-xs text-red-700 dark:text-red-400">{w.rejectionReason}</p>}
            </div>
        ) },
        { header: 'Nominal', align: 'right', cell: (w) => formatRupiah(w.amount) },
        { header: 'Bukti', cell: (w) => (w.transferProofUrl ? <a href={w.transferProofUrl} target="_blank" rel="noreferrer" className="text-sm underline underline-offset-4">Lihat</a> : '—') },
    ];

    return (
        <>
            <Head title="Pencairan Saldo" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Pencairan saldo" description={`Total pendapatan ${formatRupiah(totalIncome)}. Pencairan diproses verifikator setelah disetujui.`} />
                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardDescription>Saldo bisa dicairkan</CardDescription>
                            <CardTitle className="text-3xl tabular-nums">{formatRupiah(balance)}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="grid gap-4">
                                <FormField label="Nominal (Rp)" htmlFor="amount" error={form.errors.amount} hint={`Minimum ${formatRupiah(minimum)}.`}>
                                    <div className="flex gap-2">
                                        <Input id="amount" type="number" inputMode="numeric" min={minimum} max={balance} value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} aria-invalid={!!form.errors.amount} />
                                        <Button type="button" variant="outline" onClick={() => form.setData('amount', String(balance))} disabled={!canWithdraw}>Semua</Button>
                                    </div>
                                </FormField>
                                <FormField label="Dicairkan ke" htmlFor="method" error={form.errors.method}>
                                    <NativeSelect id="method" value={form.data.method} onChange={(e) => form.setData('method', e.target.value)}>
                                        {destinations.qris && <option value="qris">QRIS toko</option>}
                                        {destinations.bank && <option value="bank_transfer">{destinations.bank.bank} {destinations.bank.number} a.n. {destinations.bank.holder}</option>}
                                        {!destinations.bank && <option value="bank_transfer" disabled>Rekening bank belum diisi</option>}
                                    </NativeSelect>
                                </FormField>
                                <FormField label="Catatan (opsional)" htmlFor="notes" error={form.errors.notes}>
                                    <Textarea id="notes" rows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                                </FormField>
                                <Button type="submit" disabled={form.processing || !canWithdraw}>Ajukan pencairan</Button>
                                {!canWithdraw && <p className="text-xs text-muted-foreground">Saldo belum mencapai minimum pencairan.</p>}
                            </form>
                        </CardContent>
                    </Card>
                    <div className="min-w-0 lg:col-span-2">
                        <DataTable columns={columns} page={withdrawals} rowKey={(w) => w.code} empty="Belum ada pengajuan pencairan." />
                    </div>
                </div>
            </div>
        </>
    );
}

SellerWithdrawals.layout = crumbs(['Toko', urls.seller.dashboard], ['Pencairan', urls.seller.withdrawals]);

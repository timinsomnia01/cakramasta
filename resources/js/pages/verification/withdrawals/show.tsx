import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProtectedImage } from '@/components/marketplace/protected-image';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { WithdrawalRow } from '@/types';

type Props = { withdrawal: WithdrawalRow; sellerBalance: number; can: { approve: boolean; reject: boolean; markPaid: boolean } };

function PaidDialog({ url, amount }: { url: string; amount: number }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ transfer_proof: null as File | null });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, { forceFormData: true, preserveScroll: true, onSuccess: () => setOpen(false) });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild><Button size="sm">Tandai sudah dibayar</Button></DialogTrigger>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Unggah bukti transfer</DialogTitle>
                        <DialogDescription>Pastikan sudah mentransfer {formatRupiah(amount)} ke tujuan di halaman ini.</DialogDescription>
                    </DialogHeader>
                    <FormField label="Bukti transfer" htmlFor="transfer_proof" error={form.errors.transfer_proof}>
                        <Input id="transfer_proof" type="file" accept="image/*" onChange={(e) => form.setData('transfer_proof', e.target.files?.[0] ?? null)} aria-invalid={!!form.errors.transfer_proof} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                        <Button type="submit" disabled={form.processing || !form.data.transfer_proof}>Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function VerificationWithdrawalShow({ withdrawal: w, sellerBalance, can }: Props) {
    const base = `${urls.verification.withdrawals}/${w.code}`;

    return (
        <>
            <Head title={`Pencairan ${w.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Pencairan ${w.code}`}
                    description={w.storeName ?? undefined}
                    actions={
                        <>
                            {can.reject && (
                                <ReasonAction
                                    trigger={<Button size="sm" variant="outline">Tolak</Button>}
                                    title="Tolak pencairan?"
                                    description="Saldo dikembalikan ke penjual dan alasan ditampilkan kepadanya."
                                    confirmLabel="Tolak pencairan"
                                    url={`${base}/tolak`}
                                    label="Alasan penolakan"
                                />
                            )}
                            {can.approve && (
                                <ConfirmAction trigger={<Button size="sm">Setujui</Button>} title="Setujui pencairan?" description="Setelah disetujui, lakukan transfer lalu unggah bukti transfer." confirmLabel="Setujui" url={`${base}/setujui`} />
                            )}
                            {can.markPaid && <PaidDialog url={`${base}/dibayar`} amount={w.amount} />}
                        </>
                    }
                />
                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between gap-2">{formatRupiah(w.amount)}<StatusBadge status={w.status} label={w.statusLabel} /></CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 text-sm">
                            <p><span className="text-muted-foreground">Diajukan:</span> {formatDateTime(w.createdAt)}</p>
                            <p><span className="text-muted-foreground">Saldo penjual saat ini:</span> {formatRupiah(sellerBalance)}</p>
                            {w.notes && <p><span className="text-muted-foreground">Catatan penjual:</span> {w.notes}</p>}
                            {w.rejectionReason && <p className="text-red-700 dark:text-red-400">Ditolak: {w.rejectionReason}</p>}
                            {w.processedBy && <p><span className="text-muted-foreground">Diproses:</span> {w.processedBy}{w.processedAt && ` · ${formatDateTime(w.processedAt)}`}</p>}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle>Transfer ke ({w.methodLabel})</CardTitle></CardHeader>
                        <CardContent className="grid gap-3 text-sm">
                            {w.bank ? (
                                <div className="rounded-lg border p-3">
                                    <p className="text-muted-foreground">{w.bank.name} · a.n. {w.bank.holder}</p>
                                    <p className="font-mono text-lg tabular-nums">{w.bank.number}</p>
                                </div>
                            ) : (
                                <ProtectedImage src={w.qrisUrl} alt="QRIS toko" className="h-56" />
                            )}
                            {w.transferProofUrl && (
                                <div className="grid gap-1.5">
                                    <p className="text-xs text-muted-foreground">Bukti transfer</p>
                                    <ProtectedImage src={w.transferProofUrl} alt="Bukti transfer" className="h-40" />
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

VerificationWithdrawalShow.layout = (props: { withdrawal: WithdrawalRow }) =>
    crumbs(['Verifikasi', urls.verification.dashboard], ['Pencairan', urls.verification.withdrawals], [props.withdrawal.code, `${urls.verification.withdrawals}/${props.withdrawal.code}`]);

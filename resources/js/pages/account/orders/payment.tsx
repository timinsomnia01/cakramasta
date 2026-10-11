import { Head, Link, useForm } from '@inertiajs/react';
import { Copy } from 'lucide-react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProtectedImage } from '@/components/marketplace/protected-image';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { OrderDetailData } from '@/types';

type Props = {
    order: OrderDetailData;
    destination: { qrisImage: string | null; bank: { bank: string; number: string; holder: string } };
    canUpload: boolean;
};

export default function OrderPayment({ order, destination, canUpload }: Props) {
    const form = useForm({ proof: null as File | null });
    const payment = order.payment;
    const href = `${urls.account.orders}/${order.code}`;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`${href}/pembayaran`, { forceFormData: true, preserveScroll: true });
    };

    const copy = async (text: string) => {
        await navigator.clipboard.writeText(text);
        toast.success('Disalin');
    };

    return (
        <>
            <Head title={`Pembayaran ${order.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Pembayaran" description={`Pesanan ${order.code}`} actions={<Button asChild variant="outline" size="sm"><Link href={href}>Detail pesanan</Link></Button>} />

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between gap-2">
                                Transfer ke UM-Mart
                                {payment && <StatusBadge status={payment.status} label={payment.statusLabel} />}
                            </CardTitle>
                            <CardDescription>
                                {payment?.status === 'pending' && payment.expiresAt
                                    ? `Bayar sebelum ${formatDateTime(payment.expiresAt)}`
                                    : payment?.status === 'waiting_verification'
                                      ? 'Bukti pembayaran sedang diperiksa verifikator.'
                                      : undefined}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Total yang harus dibayar</p>
                                <p className="text-3xl font-semibold tabular-nums">{formatRupiah(payment?.amount ?? order.total)}</p>
                                <p className="text-xs text-muted-foreground">Transfer sesuai nominal sampai digit terakhir.</p>
                            </div>
                            {destination.qrisImage && (
                                <div>
                                    <p className="mb-1 text-sm text-muted-foreground">QRIS</p>
                                    <ProtectedImage src={destination.qrisImage} alt="QRIS UM-Mart" className="h-56" />
                                </div>
                            )}
                            <div className="rounded-lg border p-3 text-sm">
                                <p className="text-muted-foreground">Transfer bank</p>
                                <p className="font-medium">{destination.bank.bank} · a.n. {destination.bank.holder}</p>
                                <button type="button" onClick={() => copy(destination.bank.number)} className="mt-1 inline-flex items-center gap-2 font-mono text-lg tabular-nums hover:underline">
                                    {destination.bank.number} <Copy className="size-4" aria-label="Salin nomor rekening" />
                                </button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Unggah bukti pembayaran</CardTitle>
                            {payment?.rejectionReason && <CardDescription className="text-red-700 dark:text-red-400">Pembayaran sebelumnya ditolak: {payment.rejectionReason}</CardDescription>}
                        </CardHeader>
                        <CardContent>
                            {canUpload ? (
                                <form onSubmit={submit} className="grid gap-4">
                                    <FormField label="Foto / screenshot bukti" htmlFor="proof" error={form.errors.proof} hint="JPG, PNG, atau WebP, maks. 2 MB.">
                                        <Input id="proof" type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => form.setData('proof', e.target.files?.[0] ?? null)} aria-invalid={!!form.errors.proof} />
                                    </FormField>
                                    <Button type="submit" disabled={form.processing || !form.data.proof} className="w-fit">Kirim bukti</Button>
                                </form>
                            ) : payment?.hasProof ? (
                                <ProtectedImage src={payment.proofUrl} alt="Bukti pembayaran" />
                            ) : (
                                <p className="text-sm text-muted-foreground">Batas waktu pembayaran sudah lewat.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

OrderPayment.layout = (props: { order: OrderDetailData }) =>
    crumbs(['Akun saya', urls.account.dashboard], ['Pesanan', urls.account.orders], [props.order.code, `${urls.account.orders}/${props.order.code}`], ['Pembayaran', `${urls.account.orders}/${props.order.code}/pembayaran`]);

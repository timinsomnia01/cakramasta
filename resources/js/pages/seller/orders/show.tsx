import { Head, useForm } from '@inertiajs/react';
import { Truck } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { ConfirmAction } from '@/components/marketplace/action-dialogs';
import { FormField } from '@/components/marketplace/form-field';
import { OrderDetail } from '@/components/marketplace/order-detail';
import { PageHeader } from '@/components/marketplace/page-header';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { OrderDetailData } from '@/types';

type Props = { order: OrderDetailData; can: { process: boolean; ship: boolean }; requiresTracking: boolean };

function ShipDialog({ code, requiresTracking }: { code: string; requiresTracking: boolean }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ tracking_number: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`${urls.seller.orders}/${code}/kirim`, { preserveScroll: true, onSuccess: () => setOpen(false) });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild><Button size="sm"><Truck aria-hidden /> Kirim pesanan</Button></DialogTrigger>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Kirim pesanan</DialogTitle>
                        <DialogDescription>{requiresTracking ? 'Masukkan nomor resi dari kurir.' : 'Kurir ini tidak memakai nomor resi (COD / ambil di tempat).'}</DialogDescription>
                    </DialogHeader>
                    <FormField label={requiresTracking ? 'Nomor resi' : 'Nomor resi (opsional)'} htmlFor="tracking_number" error={form.errors.tracking_number}>
                        <Input id="tracking_number" value={form.data.tracking_number} onChange={(e) => form.setData('tracking_number', e.target.value)} aria-invalid={!!form.errors.tracking_number} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                        <Button type="submit" disabled={form.processing}>Tandai dikirim</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function SellerOrderShow({ order, can, requiresTracking }: Props) {
    return (
        <>
            <Head title={`Pesanan ${order.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Pesanan ${order.code}`}
                    actions={
                        <>
                            {can.process && (
                                <ConfirmAction
                                    trigger={<Button size="sm">Proses pesanan</Button>}
                                    title="Proses pesanan ini?"
                                    description="Pembeli akan melihat pesanan sedang kamu siapkan."
                                    confirmLabel="Proses"
                                    url={`${urls.seller.orders}/${order.code}/proses`}
                                />
                            )}
                            {can.ship && <ShipDialog code={order.code} requiresTracking={requiresTracking} />}
                        </>
                    }
                />
                <OrderDetail order={order} showBuyer showFees showPaymentProof={false} />
            </div>
        </>
    );
}

SellerOrderShow.layout = (props: { order: OrderDetailData }) =>
    crumbs(['Toko', urls.seller.dashboard], ['Pesanan', urls.seller.orders], [props.order.code, `${urls.seller.orders}/${props.order.code}`]);

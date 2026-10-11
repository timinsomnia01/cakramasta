import { Head, Link } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { OrderDetail } from '@/components/marketplace/order-detail';
import { PageHeader } from '@/components/marketplace/page-header';
import { ReviewDialog } from '@/components/marketplace/review-dialog';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { OrderDetailData } from '@/types';

type Props = {
    order: OrderDetailData;
    can: { pay: boolean; cancel: boolean; complete: boolean; review: boolean };
};

export default function OrderShow({ order, can }: Props) {
    const base = `${urls.account.orders}/${order.code}`;

    return (
        <>
            <Head title={`Pesanan ${order.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Pesanan ${order.code}`}
                    description={order.seller ? `Dari ${order.seller.storeName}` : undefined}
                    actions={
                        <>
                            {can.pay && (
                                <Button asChild size="sm">
                                    <Link href={`${base}/pembayaran`}><CreditCard aria-hidden /> Bayar</Link>
                                </Button>
                            )}
                            {can.complete && (
                                <ConfirmAction
                                    trigger={<Button size="sm">Pesanan diterima</Button>}
                                    title="Konfirmasi pesanan diterima?"
                                    description="Dana akan diteruskan ke penjual. Pastikan barang sudah kamu terima dengan baik."
                                    confirmLabel="Ya, sudah diterima"
                                    url={`${base}/selesai`}
                                />
                            )}
                            {can.cancel && (
                                <ReasonAction
                                    trigger={<Button size="sm" variant="outline">Batalkan</Button>}
                                    title="Batalkan pesanan?"
                                    description="Stok dikembalikan dan tagihan dibatalkan."
                                    confirmLabel="Batalkan pesanan"
                                    url={`${base}/batal`}
                                    label="Alasan (opsional)"
                                    required={false}
                                />
                            )}
                        </>
                    }
                />
                <OrderDetail
                    order={order}
                    showSeller
                    itemActions={(item) =>
                        can.review && item.reviewed === false ? (
                            <ReviewDialog url={`${base}/ulasan/${item.id}`} productName={item.productName} />
                        ) : item.reviewed ? (
                            <span className="text-xs text-muted-foreground">Sudah diulas</span>
                        ) : null
                    }
                />
            </div>
        </>
    );
}

OrderShow.layout = (props: { order: OrderDetailData }) =>
    crumbs(['Akun saya', urls.account.dashboard], ['Pesanan', urls.account.orders], [props.order.code, `${urls.account.orders}/${props.order.code}`]);

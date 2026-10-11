import { Head } from '@inertiajs/react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { OrderDetail } from '@/components/marketplace/order-detail';
import { PageHeader } from '@/components/marketplace/page-header';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { OrderDetailData, Payment } from '@/types';

type Props = { payment: Payment & { verifiedBy: string | null }; order: OrderDetailData; canReview: boolean };

export default function VerificationPaymentShow({ payment, order, canReview }: Props) {
    const base = `${urls.verification.payments}/${payment.id}`;

    return (
        <>
            <Head title={`Pembayaran ${order.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Pembayaran ${order.code}`}
                    description={`Nominal yang harus cocok: ${formatRupiah(payment.amount)}${payment.verifiedBy ? ` · diperiksa ${payment.verifiedBy}` : ''}`}
                    actions={canReview && (
                        <>
                            <ReasonAction
                                trigger={<Button size="sm" variant="outline">Tolak</Button>}
                                title="Tolak pembayaran?"
                                description="Pembeli diminta mengunggah ulang bukti yang benar (batas waktu diperpanjang 24 jam)."
                                confirmLabel="Tolak pembayaran"
                                url={`${base}/tolak`}
                                label="Alasan penolakan"
                            />
                            <ConfirmAction
                                trigger={<Button size="sm">Verifikasi</Button>}
                                title="Verifikasi pembayaran ini?"
                                description="Pesanan diteruskan ke penjual untuk diproses."
                                confirmLabel="Verifikasi"
                                url={`${base}/setujui`}
                            />
                        </>
                    )}
                />
                <OrderDetail order={order} showBuyer showSeller showFees />
            </div>
        </>
    );
}

VerificationPaymentShow.layout = (props: { payment: { id: number }; order: { code: string } }) =>
    crumbs(['Verifikasi', urls.verification.dashboard], ['Pembayaran', urls.verification.payments], [props.order.code, `${urls.verification.payments}/${props.payment.id}`]);

import { Head } from '@inertiajs/react';
import { ConfirmAction } from '@/components/marketplace/action-dialogs';
import { OrderDetail } from '@/components/marketplace/order-detail';
import { PageHeader } from '@/components/marketplace/page-header';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { OrderDetailData } from '@/types';

export default function AdminOrderShow({ order, canDelete }: { order: OrderDetailData; canDelete: boolean }) {
    return (
        <>
            <Head title={`Pesanan ${order.code}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Pesanan ${order.code}`}
                    actions={canDelete && (
                        <ConfirmAction
                            trigger={<Button size="sm" variant="destructive">Hapus pesanan</Button>}
                            title="Hapus pesanan ini?"
                            description="Hanya pesanan yang dibatalkan yang bisa dihapus. Tindakan ini permanen."
                            confirmLabel="Hapus"
                            url={`${urls.admin.orders}/${order.code}`}
                            method="delete"
                            destructive
                        />
                    )}
                />
                <OrderDetail order={order} showBuyer showSeller showFees />
            </div>
        </>
    );
}

AdminOrderShow.layout = (props: { order: OrderDetailData }) =>
    crumbs(['Admin', urls.admin.dashboard], ['Pembelian', urls.admin.orders], [props.order.code, `${urls.admin.orders}/${props.order.code}`]);

import { Head } from '@inertiajs/react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { PageHeader } from '@/components/marketplace/page-header';
import { SellerDetailView } from '@/components/marketplace/seller-detail';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { crumbs } from '@/lib/crumbs';
import { formatNumber, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { SellerDetail } from '@/types';

type Props = {
    seller: SellerDetail & { balance: number; totalIncome: number; activeOrders: number };
    can: { suspend: boolean; reactivate: boolean };
};

export default function AdminSellerShow({ seller, can }: Props) {
    const base = `${urls.admin.sellers}/${seller.id}`;

    return (
        <>
            <Head title={seller.storeName} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={seller.storeName}
                    actions={
                        <>
                            {can.suspend && (
                                <ReasonAction
                                    trigger={<Button size="sm" variant="destructive">Nonaktifkan toko</Button>}
                                    title="Nonaktifkan toko?"
                                    description="Produk toko hilang dari katalog dan pemilik tidak bisa masuk area penjual."
                                    confirmLabel="Nonaktifkan"
                                    url={`${base}/nonaktifkan`}
                                    label="Alasan penonaktifan"
                                />
                            )}
                            {can.reactivate && (
                                <ConfirmAction trigger={<Button size="sm">Aktifkan kembali</Button>} title="Aktifkan kembali toko?" description="Produk toko tampil lagi di katalog." confirmLabel="Aktifkan" url={`${base}/aktifkan`} />
                            )}
                        </>
                    }
                />
                <SellerDetailView
                    seller={seller}
                    extra={
                        <Card>
                            <CardHeader><CardTitle>Ringkasan</CardTitle></CardHeader>
                            <CardContent>
                                <dl className="grid gap-3 text-sm">
                                    {([
                                        ['Produk', formatNumber(seller.productsCount ?? 0)],
                                        ['Pesanan', formatNumber(seller.ordersCount ?? 0)],
                                        ['Pesanan berjalan', formatNumber(seller.activeOrders)],
                                        ['Total pendapatan', formatRupiah(seller.totalIncome)],
                                        ['Saldo tersedia', formatRupiah(seller.balance)],
                                    ] as const).map(([label, value]) => (
                                        <div key={label} className="flex justify-between gap-4"><dt className="text-muted-foreground">{label}</dt><dd className="font-medium tabular-nums">{value}</dd></div>
                                    ))}
                                </dl>
                            </CardContent>
                        </Card>
                    }
                />
            </div>
        </>
    );
}

AdminSellerShow.layout = (props: { seller: SellerDetail }) =>
    crumbs(['Admin', urls.admin.dashboard], ['Penjual', urls.admin.sellers], [props.seller.storeName, `${urls.admin.sellers}/${props.seller.id}`]);

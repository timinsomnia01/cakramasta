import type { ReactNode } from 'react';
import { ProductThumb } from '@/components/marketplace/product-thumb';
import { ProtectedImage } from '@/components/marketplace/protected-image';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime, formatRupiah } from '@/lib/format';
import type { OrderDetailData } from '@/types';

function Row({ label, value, strong, muted }: { label: string; value: ReactNode; strong?: boolean; muted?: boolean }) {
    return (
        <div className={`flex items-center justify-between gap-4 py-1.5 ${strong ? 'border-t pt-3 font-semibold' : ''} ${muted ? 'text-muted-foreground' : ''}`}>
            <dt>{label}</dt>
            <dd className="tabular-nums">{value}</dd>
        </div>
    );
}

/**
 * Detail pesanan yang dipakai di semua area. Rincian biaya platform & pajak hanya untuk
 * penjual / staf (showFees); pembeli cukup melihat subtotal, ongkir, dan total.
 */
export function OrderDetail({
    order,
    showFees = false,
    showBuyer = false,
    showSeller = false,
    showPaymentProof = true,
    itemActions,
}: {
    order: OrderDetailData;
    showFees?: boolean;
    showBuyer?: boolean;
    showSeller?: boolean;
    showPaymentProof?: boolean;
    itemActions?: (item: OrderDetailData['items'][number]) => ReactNode;
}) {
    return (
        <div className="grid gap-4 lg:grid-cols-3">
            <div className="flex min-w-0 flex-col gap-4 lg:col-span-2">
                <Card className="gap-0 py-0">
                    <CardHeader className="border-b py-4">
                        <CardTitle>Produk</CardTitle>
                    </CardHeader>
                    <ul className="divide-y">
                        {order.items.map((item) => (
                            <li key={item.id} className="flex flex-wrap items-center gap-3 px-6 py-3">
                                <ProductThumb src={item.image} alt={item.productName} className="size-14" />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-medium">{item.productName}</p>
                                    {item.variantName && <p className="text-xs text-muted-foreground">{item.variantName}</p>}
                                    <p className="text-xs text-muted-foreground tabular-nums">
                                        {item.quantity} × {formatRupiah(item.price)}
                                    </p>
                                </div>
                                <p className="font-medium tabular-nums">{formatRupiah(item.subtotal)}</p>
                                {itemActions && <div className="w-full sm:w-auto">{itemActions(item)}</div>}
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card className="gap-0 py-0">
                    <CardHeader className="border-b py-4">
                        <CardTitle>Riwayat status</CardTitle>
                    </CardHeader>
                    <ol className="divide-y">
                        {order.logs.map((log, index) => (
                            <li key={`${log.createdAt}-${index}`} className="flex flex-wrap items-start justify-between gap-2 px-6 py-3 text-sm">
                                <div>
                                    <StatusBadge status={log.status} label={log.statusLabel} />
                                    {log.note && <p className="mt-1 text-muted-foreground">{log.note}</p>}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {formatDateTime(log.createdAt)}
                                    {log.by && ` · ${log.by}`}
                                </p>
                            </li>
                        ))}
                    </ol>
                </Card>
            </div>

            <div className="flex min-w-0 flex-col gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between gap-2">
                            <span className="truncate">{order.code}</span>
                            <StatusBadge status={order.status} label={order.statusLabel} />
                        </CardTitle>
                        <p className="text-xs text-muted-foreground">Dibuat {formatDateTime(order.createdAt)}</p>
                    </CardHeader>
                    <CardContent>
                        <dl className="text-sm">
                            <Row label="Subtotal" value={formatRupiah(order.subtotal)} />
                            <Row label="Ongkir" value={formatRupiah(order.shippingCost)} />
                            <Row label="Total" value={formatRupiah(order.total)} strong />
                            {showFees && (
                                <>
                                    <Row label="Biaya platform" value={`− ${formatRupiah(order.platformFee)}`} muted />
                                    <Row label="Pajak" value={`− ${formatRupiah(order.taxAmount)}`} muted />
                                    <Row label="Pendapatan penjual" value={formatRupiah(order.sellerIncome)} strong />
                                </>
                            )}
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Pengiriman</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-1 text-sm">
                        <p className="font-medium">{order.recipient.name}</p>
                        <p className="text-muted-foreground">{order.recipient.phone}</p>
                        <p>{order.recipient.address}</p>
                        <p className="pt-2 text-muted-foreground">
                            Kurir: <span className="text-foreground">{order.courier}</span>
                        </p>
                        {order.trackingNumber && (
                            <p className="text-muted-foreground">
                                No. resi: <span className="font-mono text-foreground">{order.trackingNumber}</span>
                            </p>
                        )}
                        {order.notes && <p className="pt-2 text-muted-foreground">Catatan: {order.notes}</p>}
                        {order.cancelReason && <p className="pt-2 text-red-700 dark:text-red-400">Alasan batal: {order.cancelReason}</p>}
                    </CardContent>
                </Card>

                {(showBuyer || showSeller) && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Pihak terkait</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            {showBuyer && order.buyer && (
                                <div>
                                    <p className="text-xs text-muted-foreground">Pembeli</p>
                                    <p className="font-medium">{order.buyer.name}</p>
                                    <p className="text-muted-foreground">{order.buyer.email}</p>
                                    {order.buyer.phone && <p className="text-muted-foreground">{order.buyer.phone}</p>}
                                </div>
                            )}
                            {showSeller && order.seller && (
                                <div>
                                    <p className="text-xs text-muted-foreground">Toko</p>
                                    <p className="font-medium">{order.seller.storeName}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {order.payment && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between gap-2">
                                Pembayaran
                                <StatusBadge status={order.payment.status} label={order.payment.statusLabel} />
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <p className="text-muted-foreground">
                                {order.payment.methodLabel} · {formatRupiah(order.payment.amount)}
                            </p>
                            {order.payment.expiresAt && order.payment.status === 'pending' && (
                                <p className={order.payment.isExpired ? 'text-red-700 dark:text-red-400' : 'text-muted-foreground'}>
                                    {order.payment.isExpired ? 'Kedaluwarsa' : 'Bayar sebelum'} {formatDateTime(order.payment.expiresAt)}
                                </p>
                            )}
                            {order.payment.rejectionReason && (
                                <p className="text-red-700 dark:text-red-400">Ditolak: {order.payment.rejectionReason}</p>
                            )}
                            {showPaymentProof && order.payment.hasProof && (
                                <ProtectedImage src={order.payment.proofUrl} alt="Bukti pembayaran" className="h-40" />
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </div>
    );
}

import { Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { formatDateTime, formatRupiah } from '@/lib/format';
import type { OrderRow } from '@/types';

type Props = {
    orders: OrderRow[];
    /** Kolom nama pihak lain: pembeli (sisi penjual/admin) atau toko (sisi pembeli). */
    party?: 'buyer' | 'store' | null;
    showIncome?: boolean;
    empty?: string;
};

export function OrdersTable({
    orders,
    party = null,
    showIncome = false,
    empty = 'Belum ada pesanan.',
}: Props) {
    if (orders.length === 0) {
        return (
            <p className="px-6 py-8 text-center text-sm text-muted-foreground">
                {empty}
            </p>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b text-left text-xs text-muted-foreground">
                        <th className="px-6 py-2 font-medium">Pesanan</th>
                        {party && (
                            <th className="px-3 py-2 font-medium">
                                {party === 'buyer' ? 'Pembeli' : 'Toko'}
                            </th>
                        )}
                        <th className="px-3 py-2 font-medium">Status</th>
                        <th className="px-3 py-2 text-right font-medium">
                            {showIncome ? 'Pendapatan' : 'Total'}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {orders.map((order) => (
                        <tr
                            key={order.code}
                            className="border-b last:border-0 hover:bg-accent/40"
                        >
                            <td className="px-6 py-3">
                                <Link
                                    href={order.href}
                                    className="font-medium underline-offset-4 hover:underline"
                                >
                                    {order.code}
                                </Link>
                                <p className="max-w-64 truncate text-xs text-muted-foreground">
                                    {order.items[0]?.name}
                                    {order.itemsCount > 1 &&
                                        ` +${order.itemsCount - 1} lainnya`}
                                    {' · '}
                                    {formatDateTime(order.createdAt)}
                                </p>
                            </td>
                            {party && (
                                <td className="px-3 py-3">
                                    {party === 'buyer'
                                        ? order.buyerName
                                        : order.storeName}
                                </td>
                            )}
                            <td className="px-3 py-3">
                                <StatusBadge
                                    status={order.status}
                                    label={order.statusLabel}
                                />
                            </td>
                            <td className="px-3 py-3 text-right tabular-nums">
                                {formatRupiah(
                                    showIncome
                                        ? order.sellerIncome
                                        : order.total,
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

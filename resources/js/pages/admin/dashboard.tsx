import { Head, Link } from '@inertiajs/react';
import {
    Banknote,
    Box,
    CreditCard,
    Download,
    Receipt,
    Store,
    TrendingUp,
    Users,
} from 'lucide-react';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatCard } from '@/components/marketplace/stat-card';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { VisitorsChart } from '@/components/marketplace/visitors-chart';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import {
    formatDateTime,
    formatNumber,
    formatRupiah,
    formatRupiahCompact,
} from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';

type Props = {
    visitors: {
        today: number;
        week: number;
        month: number;
        total: number;
        daily: { date: string; visitors: number }[];
    };
    pending: {
        sellers: number;
        products: number;
        payments: number;
        withdrawals: number;
    };
    active: {
        buyers: number;
        sellers: number;
        products: number;
        verifikators: number;
    };
    sales: {
        ordersCompleted: number;
        gmv: number;
        platformFee: number;
        tax: number;
    };
    latestBuyers: {
        orderCode: string;
        buyerName: string | null;
        buyerEmail: string | null;
        storeName: string | null;
        total: number;
        status: string;
        statusLabel: string;
        createdAt: string;
        href: string;
    }[];
};

export default function AdminDashboard({
    visitors,
    pending,
    active,
    sales,
    latestBuyers,
}: Props) {
    return (
        <>
            <Head title="Dashboard Admin" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Dashboard admin"
                    description="Ringkasan aktivitas UM-Mart."
                    actions={
                        <Button asChild variant="outline" size="sm">
                            <a href="/admin/pembelian/export">
                                <Download aria-hidden /> Ekspor log pembelian
                            </a>
                        </Button>
                    }
                />

                <section aria-label="Perlu verifikasi" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Pembayaran menunggu"
                        value={formatNumber(pending.payments)}
                        icon={CreditCard}
                        href={urls.verification.payments}
                        attention={pending.payments > 0}
                    />
                    <StatCard
                        label="Penjual menunggu"
                        value={formatNumber(pending.sellers)}
                        icon={Store}
                        href={`${urls.verification.sellers}?status=pending`}
                        attention={pending.sellers > 0}
                    />
                    <StatCard
                        label="Produk menunggu"
                        value={formatNumber(pending.products)}
                        icon={Box}
                        href={`${urls.verification.products}?status=pending`}
                        attention={pending.products > 0}
                    />
                    <StatCard
                        label="Pencairan menunggu"
                        value={formatNumber(pending.withdrawals)}
                        icon={Banknote}
                        href={`${urls.verification.withdrawals}?status=pending`}
                        attention={pending.withdrawals > 0}
                    />
                </section>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="min-w-0 gap-4 lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Pengunjung unik per hari</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                14 hari terakhir
                            </p>
                        </CardHeader>
                        <div className="px-6">
                            <VisitorsChart data={visitors.daily} />
                        </div>
                    </Card>

                    <Card className="gap-0 py-0">
                        <CardHeader className="border-b py-4">
                            <CardTitle>Pengunjung</CardTitle>
                        </CardHeader>
                        <ul className="divide-y text-sm">
                            {[
                                ['Hari ini', visitors.today],
                                ['7 hari terakhir', visitors.week],
                                ['30 hari terakhir', visitors.month],
                                ['Sepanjang waktu', visitors.total],
                            ].map(([label, count]) => (
                                <li
                                    key={label}
                                    className="flex items-center justify-between px-6 py-3"
                                >
                                    <span className="text-muted-foreground">
                                        {label}
                                    </span>
                                    <span className="font-medium tabular-nums">
                                        {formatNumber(Number(count))}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </Card>
                </div>

                <section aria-label="Penjualan" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Nilai transaksi (GMV)"
                        value={formatRupiahCompact(sales.gmv)}
                        hint={formatRupiah(sales.gmv)}
                        icon={TrendingUp}
                    />
                    <StatCard
                        label="Pesanan selesai"
                        value={formatNumber(sales.ordersCompleted)}
                        icon={Receipt}
                        href={`${urls.admin.orders}?status=completed`}
                    />
                    <StatCard
                        label="Biaya platform"
                        value={formatRupiahCompact(sales.platformFee)}
                        hint={formatRupiah(sales.platformFee)}
                    />
                    <StatCard
                        label="Pajak terkumpul"
                        value={formatRupiahCompact(sales.tax)}
                        hint={formatRupiah(sales.tax)}
                    />
                </section>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="min-w-0 gap-0 py-0 lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                            <CardTitle>Pembeli terakhir</CardTitle>
                            <Button asChild variant="ghost" size="sm">
                                <Link href={urls.admin.orders}>Log pembelian</Link>
                            </Button>
                        </CardHeader>
                        {latestBuyers.length === 0 ? (
                            <p className="px-6 py-8 text-center text-sm text-muted-foreground">
                                Belum ada pembelian.
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b text-left text-xs text-muted-foreground">
                                            <th className="px-6 py-2 font-medium">Pembeli</th>
                                            <th className="px-3 py-2 font-medium">Toko</th>
                                            <th className="px-3 py-2 font-medium">Status</th>
                                            <th className="px-3 py-2 text-right font-medium">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {latestBuyers.map((row) => (
                                            <tr key={row.orderCode} className="border-b last:border-0 hover:bg-accent/40">
                                                <td className="px-6 py-3">
                                                    <Link href={row.href} className="font-medium underline-offset-4 hover:underline">
                                                        {row.buyerName}
                                                    </Link>
                                                    <p className="text-xs text-muted-foreground">
                                                        {row.orderCode} · {formatDateTime(row.createdAt)}
                                                    </p>
                                                </td>
                                                <td className="px-3 py-3">{row.storeName}</td>
                                                <td className="px-3 py-3">
                                                    <StatusBadge status={row.status} label={row.statusLabel} />
                                                </td>
                                                <td className="px-3 py-3 text-right tabular-nums">
                                                    {formatRupiah(row.total)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Card>

                    <Card className="gap-0 py-0">
                        <CardHeader className="border-b py-4">
                            <CardTitle className="flex items-center gap-2">
                                <Users className="size-4" aria-hidden /> Akun & katalog aktif
                            </CardTitle>
                        </CardHeader>
                        <ul className="divide-y text-sm">
                            {[
                                ['Pembeli', active.buyers, null],
                                ['Penjual', active.sellers, urls.admin.sellers],
                                ['Produk', active.products, urls.admin.products],
                                ['Verifikator', active.verifikators, urls.admin.verifikators],
                            ].map(([label, count, href]) => (
                                <li key={String(label)}>
                                    {href ? (
                                        <Link href={String(href)} className="flex items-center justify-between px-6 py-3 hover:bg-accent/40">
                                            <span>{label}</span>
                                            <span className="font-medium tabular-nums">{formatNumber(Number(count))}</span>
                                        </Link>
                                    ) : (
                                        <div className="flex items-center justify-between px-6 py-3">
                                            <span>{label}</span>
                                            <span className="font-medium tabular-nums">{formatNumber(Number(count))}</span>
                                        </div>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </Card>
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Admin', href: urls.admin.dashboard }],
};

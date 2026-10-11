import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    Banknote,
    PackageCheck,
    Plus,
    Truck,
    Wallet,
} from 'lucide-react';
import { OrdersTable } from '@/components/marketplace/orders-table';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatCard } from '@/components/marketplace/stat-card';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { OrderRow } from '@/types';

type Props = {
    store: { name: string; slug: string };
    stats: {
        balance: number;
        totalIncome: number;
        toProcess: number;
        processing: number;
        shipped: number;
        completed: number;
        products: {
            approved: number;
            pending: number;
            rejected: number;
            draft: number;
        };
    };
    lowStock: { id: number; name: string; stock: number }[];
    recentOrders: OrderRow[];
};

const PRODUCT_STATES: {
    key: keyof Props['stats']['products'];
    label: string;
}[] = [
    { key: 'approved', label: 'Aktif' },
    { key: 'pending', label: 'Menunggu verifikasi' },
    { key: 'rejected', label: 'Ditolak' },
    { key: 'draft', label: 'Draft' },
];

export default function SellerDashboard({
    store,
    stats,
    lowStock,
    recentOrders,
}: Props) {
    return (
        <>
            <Head title="Dashboard Toko" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={store.name}
                    description="Dashboard toko"
                    actions={
                        <Button asChild size="sm">
                            <Link href={urls.seller.newProduct}>
                                <Plus aria-hidden /> Tambah produk
                            </Link>
                        </Button>
                    }
                />

                <Card className="gap-0 py-0">
                    <div className="flex flex-wrap items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <p className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Wallet className="size-4" aria-hidden /> Saldo
                                bisa dicairkan
                            </p>
                            <p className="mt-1 text-3xl font-semibold tabular-nums">
                                {formatRupiah(stats.balance)}
                            </p>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Total pendapatan {formatRupiah(stats.totalIncome)}
                            </p>
                        </div>
                        <Button asChild variant="outline">
                            <Link href={urls.seller.withdrawals}>
                                <Banknote aria-hidden /> Cairkan saldo
                            </Link>
                        </Button>
                    </div>
                </Card>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Perlu diproses"
                        value={formatNumber(stats.toProcess)}
                        hint="Sudah dibayar pembeli"
                        icon={PackageCheck}
                        href={`${urls.seller.orders}?status=paid`}
                        attention={stats.toProcess > 0}
                    />
                    <StatCard
                        label="Siap dikirim"
                        value={formatNumber(stats.processing)}
                        hint="Sedang kamu siapkan"
                        icon={PackageCheck}
                        href={`${urls.seller.orders}?status=processing`}
                        attention={stats.processing > 0}
                    />
                    <StatCard
                        label="Dalam pengiriman"
                        value={formatNumber(stats.shipped)}
                        icon={Truck}
                        href={`${urls.seller.orders}?status=shipped`}
                    />
                    <StatCard
                        label="Pesanan selesai"
                        value={formatNumber(stats.completed)}
                        href={`${urls.seller.orders}?status=completed`}
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="min-w-0 gap-0 py-0 lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                            <CardTitle>Pesanan terbaru</CardTitle>
                            <Button asChild variant="ghost" size="sm">
                                <Link href={urls.seller.orders}>Semua pesanan</Link>
                            </Button>
                        </CardHeader>
                        <OrdersTable
                            orders={recentOrders}
                            party="buyer"
                            showIncome
                            empty="Belum ada pesanan masuk."
                        />
                    </Card>

                    <div className="flex flex-col gap-4">
                        <Card className="gap-0 py-0">
                            <CardHeader className="border-b py-4">
                                <CardTitle>Produk</CardTitle>
                            </CardHeader>
                            <ul className="divide-y text-sm">
                                {PRODUCT_STATES.map(({ key, label }) => (
                                    <li key={key}>
                                        <Link
                                            href={`${urls.seller.products}?status=${key}`}
                                            className="flex items-center justify-between px-6 py-3 hover:bg-accent/40"
                                        >
                                            <span>{label}</span>
                                            <span className="font-medium tabular-nums">
                                                {formatNumber(stats.products[key])}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </Card>

                        <Card className="gap-0 py-0">
                            <CardHeader className="border-b py-4">
                                <CardTitle className="flex items-center gap-2">
                                    <AlertTriangle
                                        className="size-4 text-amber-600 dark:text-amber-400"
                                        aria-hidden
                                    />
                                    Stok hampir habis
                                </CardTitle>
                            </CardHeader>
                            {lowStock.length === 0 ? (
                                <p className="px-6 py-6 text-center text-sm text-muted-foreground">
                                    Semua stok aman.
                                </p>
                            ) : (
                                <ul className="divide-y text-sm">
                                    {lowStock.map((product) => (
                                        <li
                                            key={product.id}
                                            className="flex items-center justify-between gap-3 px-6 py-3"
                                        >
                                            <Link
                                                href={`/toko/produk/${product.id}/ubah`}
                                                className="truncate underline-offset-4 hover:underline"
                                            >
                                                {product.name}
                                            </Link>
                                            <span className="shrink-0 font-medium tabular-nums">
                                                {product.stock === 0
                                                    ? 'Habis'
                                                    : `Sisa ${product.stock}`}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

SellerDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard toko', href: urls.seller.dashboard }],
};

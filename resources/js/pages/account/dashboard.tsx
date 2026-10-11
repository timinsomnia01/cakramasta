import { Head, Link } from '@inertiajs/react';
import {
    Bookmark,
    CheckCircle2,
    Clock,
    PackageCheck,
    Store,
    Truck,
} from 'lucide-react';
import { OrdersTable } from '@/components/marketplace/orders-table';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatCard } from '@/components/marketplace/stat-card';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatNumber } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { OrderRow } from '@/types';

type Props = {
    stats: {
        unpaid: number;
        inProgress: number;
        shipped: number;
        completed: number;
        saved: number;
    };
    recentOrders: OrderRow[];
    seller: {
        status: string;
        statusLabel: string;
        storeName: string;
    } | null;
};

function SellerCta({ seller }: { seller: Props['seller'] }) {
    if (!seller) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Store className="size-4" aria-hidden /> Jual barang di
                        UM-Mart
                    </CardTitle>
                    <CardDescription>
                        Daftarkan tokomu dengan KTP, foto selfie, dan QRIS.
                        Verifikasi biasanya selesai dalam 1-2 hari kerja.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button asChild>
                        <Link href={urls.account.sellerRegistration}>
                            Daftar jadi penjual
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Store className="size-4" aria-hidden /> {seller.storeName}
                </CardTitle>
                <CardDescription>Status pendaftaran toko</CardDescription>
            </CardHeader>
            <CardContent className="flex flex-wrap items-center gap-3">
                <StatusBadge
                    status={seller.status}
                    label={seller.statusLabel}
                />
                {seller.status === 'approved' ? (
                    <Button asChild size="sm">
                        <Link href={urls.seller.dashboard}>Buka dashboard toko</Link>
                    </Button>
                ) : (
                    <Button asChild size="sm" variant="outline">
                        <Link href={urls.account.sellerRegistration}>
                            {seller.status === 'rejected'
                                ? 'Perbaiki pendaftaran'
                                : 'Lihat detail'}
                        </Link>
                    </Button>
                )}
            </CardContent>
        </Card>
    );
}

export default function AccountDashboard({
    stats,
    recentOrders,
    seller,
}: Props) {
    return (
        <>
            <Head title="Akun Saya" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Akun saya"
                    description="Ringkasan pesanan dan toko kamu."
                    actions={
                        <Button asChild variant="outline" size="sm">
                            <Link href={urls.account.personalData}>
                                Ubah data diri
                            </Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <StatCard
                        label="Belum dibayar"
                        value={formatNumber(stats.unpaid)}
                        hint="Menunggu bayar / verifikasi"
                        icon={Clock}
                        href={`${urls.account.orders}?status=pending_payment`}
                        attention={stats.unpaid > 0}
                    />
                    <StatCard
                        label="Diproses"
                        value={formatNumber(stats.inProgress)}
                        hint="Sudah dibayar, disiapkan penjual"
                        icon={PackageCheck}
                        href={`${urls.account.orders}?status=processing`}
                    />
                    <StatCard
                        label="Dikirim"
                        value={formatNumber(stats.shipped)}
                        hint="Dalam perjalanan"
                        icon={Truck}
                        href={`${urls.account.orders}?status=shipped`}
                    />
                    <StatCard
                        label="Selesai"
                        value={formatNumber(stats.completed)}
                        icon={CheckCircle2}
                        href={`${urls.account.orders}?status=completed`}
                    />
                    <StatCard
                        label="Produk tersimpan"
                        value={formatNumber(stats.saved)}
                        icon={Bookmark}
                        href={urls.account.saved}
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="min-w-0 gap-0 py-0 lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                            <CardTitle>Pesanan terbaru</CardTitle>
                            <Button asChild variant="ghost" size="sm">
                                <Link href={urls.account.orders}>
                                    Semua pesanan
                                </Link>
                            </Button>
                        </CardHeader>
                        <OrdersTable
                            orders={recentOrders}
                            party="store"
                            empty="Kamu belum punya pesanan. Yuk mulai belanja!"
                        />
                    </Card>
                    <SellerCta seller={seller} />
                </div>
            </div>
        </>
    );
}

AccountDashboard.layout = {
    breadcrumbs: [{ title: 'Akun saya', href: urls.account.dashboard }],
};

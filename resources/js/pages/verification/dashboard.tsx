import { Head } from '@inertiajs/react';
import { Banknote, Box, CreditCard, Store } from 'lucide-react';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatCard } from '@/components/marketplace/stat-card';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';

type Props = {
    queues: {
        sellers: number;
        products: number;
        payments: number;
        withdrawals: number;
    };
    verifiedToday: { payments: number; sellers: number; products: number };
};

export default function VerificationDashboard({
    queues,
    verifiedToday,
}: Props) {
    const total =
        queues.sellers + queues.products + queues.payments + queues.withdrawals;

    return (
        <>
            <Head title="Dashboard Verifikasi" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Antrean verifikasi"
                    description={
                        total === 0
                            ? 'Semua antrean kosong. Kerja bagus!'
                            : `${formatNumber(total)} item menunggu ditinjau.`
                    }
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Pembayaran"
                        value={formatNumber(queues.payments)}
                        hint="Bukti bayar menunggu dicek"
                        icon={CreditCard}
                        href={urls.verification.payments}
                        attention={queues.payments > 0}
                    />
                    <StatCard
                        label="Pendaftaran penjual"
                        value={formatNumber(queues.sellers)}
                        hint="KTP, selfie, dan QRIS"
                        icon={Store}
                        href={`${urls.verification.sellers}?status=pending`}
                        attention={queues.sellers > 0}
                    />
                    <StatCard
                        label="Produk"
                        value={formatNumber(queues.products)}
                        hint="Produk baru / diubah"
                        icon={Box}
                        href={`${urls.verification.products}?status=pending`}
                        attention={queues.products > 0}
                    />
                    <StatCard
                        label="Pencairan"
                        value={formatNumber(queues.withdrawals)}
                        hint="Permintaan cair saldo"
                        icon={Banknote}
                        href={`${urls.verification.withdrawals}?status=pending`}
                        attention={queues.withdrawals > 0}
                    />
                </div>

                <Card className="gap-0 py-0 lg:max-w-xl">
                    <CardHeader className="border-b py-4">
                        <CardTitle>Selesai diverifikasi hari ini</CardTitle>
                    </CardHeader>
                    <ul className="divide-y text-sm">
                        {[
                            ['Pembayaran', verifiedToday.payments],
                            ['Penjual', verifiedToday.sellers],
                            ['Produk', verifiedToday.products],
                        ].map(([label, count]) => (
                            <li
                                key={label}
                                className="flex items-center justify-between px-6 py-3"
                            >
                                <span>{label}</span>
                                <span className="font-medium tabular-nums">
                                    {formatNumber(Number(count))}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>
        </>
    );
}

VerificationDashboard.layout = {
    breadcrumbs: [{ title: 'Verifikasi', href: urls.verification.dashboard }],
};

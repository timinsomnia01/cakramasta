import type { ReactNode } from 'react';
import { ProtectedImage } from '@/components/marketplace/protected-image';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/format';
import type { SellerDetail } from '@/types';

function Info({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <dt className="text-xs text-muted-foreground">{label}</dt>
            <dd className="text-sm">{children || '—'}</dd>
        </div>
    );
}

/** Profil + dokumen penjual untuk verifikator dan admin. */
export function SellerDetailView({ seller, extra }: { seller: SellerDetail; extra?: ReactNode }) {
    return (
        <div className="grid gap-4 lg:grid-cols-3">
            <div className="flex min-w-0 flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                            {seller.storeName}
                            <StatusBadge status={seller.status} label={seller.statusLabel} />
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {seller.rejectionReason && (
                            <p className="mb-4 rounded-md border border-red-600/30 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-950/40 dark:text-red-300">
                                {seller.status === 'suspended' ? 'Alasan penonaktifan' : 'Alasan penolakan'}: {seller.rejectionReason}
                            </p>
                        )}
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Info label="Pemilik">{seller.ownerName}{seller.ownerEmail && <span className="block text-muted-foreground">{seller.ownerEmail}</span>}</Info>
                            <Info label="Nomor HP">{seller.phone}</Info>
                            <Info label="Nomor KTP"><span className="font-mono">{seller.ktpNumber}</span></Info>
                            <Info label="Rekening">{seller.bank ? `${seller.bank.name} ${seller.bank.number} a.n. ${seller.bank.holder}` : null}</Info>
                            <Info label="Alamat">{seller.address}</Info>
                            <Info label="Deskripsi">{seller.description}</Info>
                            <Info label="Didaftarkan">{formatDateTime(seller.createdAt)}</Info>
                            <Info label="Diverifikasi">{seller.verifiedAt ? `${formatDateTime(seller.verifiedAt)}${seller.verifiedBy ? ` oleh ${seller.verifiedBy}` : ''}` : null}</Info>
                        </dl>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Dokumen</CardTitle></CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-3">
                        {([['KTP', seller.documents.ktp], ['Selfie dengan KTP', seller.documents.selfie], ['QRIS', seller.documents.qris]] as const).map(([label, src]) => (
                            <div key={label} className="grid gap-1.5">
                                <p className="text-xs text-muted-foreground">{label}</p>
                                <ProtectedImage src={src} alt={label} className="h-40" />
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
            {extra && <div className="flex min-w-0 flex-col gap-4">{extra}</div>}
        </div>
    );
}

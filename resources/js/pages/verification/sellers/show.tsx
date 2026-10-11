import { Head } from '@inertiajs/react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { PageHeader } from '@/components/marketplace/page-header';
import { SellerDetailView } from '@/components/marketplace/seller-detail';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { SellerDetail } from '@/types';

export default function VerificationSellerShow({ seller, canReview }: { seller: SellerDetail; canReview: boolean }) {
    const base = `${urls.verification.sellers}/${seller.id}`;

    return (
        <>
            <Head title={`Tinjau ${seller.storeName}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title={`Tinjau ${seller.storeName}`}
                    actions={canReview && (
                        <>
                            <ReasonAction
                                trigger={<Button size="sm" variant="outline">Tolak</Button>}
                                title="Tolak pendaftaran?"
                                description="Alasan ditampilkan ke calon penjual agar bisa memperbaiki dan mengajukan ulang."
                                confirmLabel="Tolak pendaftaran"
                                url={`${base}/tolak`}
                                label="Alasan penolakan"
                            />
                            <ConfirmAction
                                trigger={<Button size="sm">Setujui</Button>}
                                title="Setujui penjual ini?"
                                description="Toko langsung aktif dan bisa menjual produk."
                                confirmLabel="Setujui"
                                url={`${base}/setujui`}
                            />
                        </>
                    )}
                />
                <SellerDetailView seller={seller} />
            </div>
        </>
    );
}

VerificationSellerShow.layout = (props: { seller: SellerDetail }) =>
    crumbs(['Verifikasi', urls.verification.dashboard], ['Penjual', urls.verification.sellers], [props.seller.storeName, `${urls.verification.sellers}/${props.seller.id}`]);

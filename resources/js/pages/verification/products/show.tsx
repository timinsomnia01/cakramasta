import { Head } from '@inertiajs/react';
import { ConfirmAction, ReasonAction } from '@/components/marketplace/action-dialogs';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductDetailView } from '@/components/marketplace/product-detail';
import type { ProductDetailData } from '@/components/marketplace/product-detail';
import { Button } from '@/components/ui/button';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';

type Props = { product: ProductDetailData & { id: number }; canReview: boolean };

export default function VerificationProductShow({ product, canReview }: Props) {
    const base = `${urls.verification.products}/${product.id}`;

    return (
        <>
            <Head title={`Tinjau ${product.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader
                    title="Tinjau produk"
                    actions={canReview && (
                        <>
                            <ReasonAction
                                trigger={<Button size="sm" variant="outline">Tolak</Button>}
                                title="Tolak produk?"
                                description="Penjual melihat alasan ini dan bisa memperbaiki produknya."
                                confirmLabel="Tolak produk"
                                url={`${base}/tolak`}
                                label="Alasan penolakan"
                            />
                            <ConfirmAction
                                trigger={<Button size="sm">Setujui</Button>}
                                title="Setujui produk ini?"
                                description="Produk langsung tampil di katalog."
                                confirmLabel="Setujui"
                                url={`${base}/setujui`}
                            />
                        </>
                    )}
                />
                <ProductDetailView product={product} />
            </div>
        </>
    );
}

VerificationProductShow.layout = (props: { product: { id: number; name: string } }) =>
    crumbs(['Verifikasi', urls.verification.dashboard], ['Produk', urls.verification.products], [props.product.name, `${urls.verification.products}/${props.product.id}`]);

import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductForm } from '@/components/marketplace/product-form';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { FormProduct } from '@/types';

type Props = {
    product: FormProduct | null;
    categories: { id: number; name: string; group: string }[];
    limits: { maxImages: number; maxImageKb: number };
};

export default function SellerProductForm({ product, categories, limits }: Props) {
    return (
        <>
            <Head title={product ? 'Ubah Produk' : 'Tambah Produk'} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title={product ? `Ubah ${product.name}` : 'Tambah produk'} />
                <ProductForm
                    product={product}
                    categories={categories}
                    limits={limits}
                    submitUrl={product ? `${urls.seller.products}/${product.id}` : urls.seller.products}
                    cancelUrl={urls.seller.products}
                />
            </div>
        </>
    );
}

SellerProductForm.layout = crumbs(['Toko', urls.seller.dashboard], ['Produk', urls.seller.products], ['Formulir', '#']);

import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProductForm } from '@/components/marketplace/product-form';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';
import type { FormProduct, Option } from '@/types';

type Props = {
    product: FormProduct | null;
    categories: { id: number; name: string; group: string }[];
    sellers: { id: number; name: string }[];
    statuses: Option[];
    limits: { maxImages: number; maxImageKb: number };
};

export default function AdminProductForm({ product, categories, sellers, statuses, limits }: Props) {
    return (
        <>
            <Head title={product ? 'Ubah Produk' : 'Tambah Produk'} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title={product ? `Ubah ${product.name}` : 'Tambah produk'} />
                <ProductForm
                    product={product}
                    categories={categories}
                    limits={limits}
                    admin={{ sellers, statuses }}
                    submitUrl={product ? `${urls.admin.products}/${product.id}` : urls.admin.products}
                    cancelUrl={urls.admin.products}
                />
            </div>
        </>
    );
}

AdminProductForm.layout = crumbs(['Admin', urls.admin.dashboard], ['Produk', urls.admin.products], ['Formulir', '#']);

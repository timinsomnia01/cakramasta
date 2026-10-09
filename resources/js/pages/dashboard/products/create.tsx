import { Head, usePage } from '@inertiajs/react';
import ProductForm from '@/components/product-form';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { route } from 'ziggy-js';
import type { BreadcrumbItem } from '@/types';

export default function ProductsCreate() {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;

    return (
        <>
            <Head title="Tambah produk" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Tambah produk</h1>
                <ProductForm slug={currentTeam.slug} />
            </div>
        </>
    );
}

ProductsCreate.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        { title: 'Dashboard', href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/' },
        { title: 'Products', href: props.currentTeam ? productsIndex(props.currentTeam.slug) : '/products' },
        { title: 'Tambah', href: props.currentTeam ? route('products.create', { current_team: props.currentTeam.slug }) : '/products/create' },
    ] as BreadcrumbItem[],
});

import { Head, usePage } from '@inertiajs/react';
import ProductForm, { type Product } from '@/components/product-form';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { route } from 'ziggy-js';
import type { BreadcrumbItem } from '@/types';

export default function ProductsEdit({ product }: { product: Product }) {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;

    return (
        <>
            <Head title={`Edit ${product.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Edit produk</h1>
                <ProductForm slug={currentTeam.slug} product={product} />
            </div>
        </>
    );
}

ProductsEdit.layout = (props: { currentTeam?: { slug: string } | null; product?: Product }) => ({
    breadcrumbs: [
        { title: 'Dashboard', href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/' },
        { title: 'Products', href: props.currentTeam ? productsIndex(props.currentTeam.slug) : '/products' },
        {
            title: 'Edit',
            href:
                props.currentTeam && props.product
                    ? route('products.edit', { current_team: props.currentTeam.slug, product: props.product.slug })
                    : '/products',
        },
    ] as BreadcrumbItem[],
});

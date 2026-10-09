import { Head, usePage } from '@inertiajs/react';
import CategoryForm from '@/components/category-form';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/categories';
import { route } from 'ziggy-js';
import type { BreadcrumbItem } from '@/types';

export default function CategoriesCreate() {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;

    return (
        <>
            <Head title="Tambah kategori" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Tambah kategori</h1>
                <CategoryForm slug={currentTeam.slug} />
            </div>
        </>
    );
}

CategoriesCreate.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        { title: 'Dashboard', href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/' },
        { title: 'Categories', href: props.currentTeam ? categoriesIndex(props.currentTeam.slug) : '/categories' },
        { title: 'Tambah', href: props.currentTeam ? route('categories.create', { current_team: props.currentTeam.slug }) : '/categories/create' },
    ] as BreadcrumbItem[],
});

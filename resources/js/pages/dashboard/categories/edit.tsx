import { Head, usePage } from '@inertiajs/react';
import CategoryForm, { type Category } from '@/components/category-form';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/categories';
import { route } from 'ziggy-js';
import type { BreadcrumbItem } from '@/types';

export default function CategoriesEdit({ category }: { category: Category }) {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;

    return (
        <>
            <Head title={`Edit ${category.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Edit kategori</h1>
                <CategoryForm slug={currentTeam.slug} category={category} />
            </div>
        </>
    );
}

CategoriesEdit.layout = (props: { currentTeam?: { slug: string } | null; category?: Category }) => ({
    breadcrumbs: [
        { title: 'Dashboard', href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/' },
        { title: 'Categories', href: props.currentTeam ? categoriesIndex(props.currentTeam.slug) : '/categories' },
        {
            title: 'Edit',
            href:
                props.currentTeam && props.category
                    ? route('categories.edit', { current_team: props.currentTeam.slug, category: props.category.slug })
                    : '/categories',
        },
    ] as BreadcrumbItem[],
});

import { Head, Link } from '@inertiajs/react';
import { StoreLayout } from '@/components/store/store-layout';
import { formatNumber } from '@/lib/format';

type Category = { id: number; name: string; slug: string; description: string | null; image: string | null; productCount: number; href: string };

export default function CategoriesIndex({ categories }: { categories: Category[] }) {
    return (
        <StoreLayout>
            <Head title="Kategori" />
            <h1 className="mb-1 text-2xl font-semibold">Kategori</h1>
            <p className="mb-6 text-sm text-muted-foreground">Jelajahi produk berdasarkan kategori.</p>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {categories.map((category) => (
                    <Link key={category.id} href={category.href} className="group min-w-0 overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md">
                        <div className="aspect-[16/9] bg-muted">
                            {category.image && <img src={category.image} alt="" loading="lazy" className="size-full object-cover transition-transform duration-300 group-hover:scale-105" />}
                        </div>
                        <div className="space-y-1 p-4">
                            <p className="font-medium">{category.name}</p>
                            {category.description && <p className="line-clamp-2 text-sm text-muted-foreground">{category.description}</p>}
                            <p className="text-xs text-muted-foreground">{formatNumber(category.productCount)} produk</p>
                        </div>
                    </Link>
                ))}
            </div>
        </StoreLayout>
    );
}

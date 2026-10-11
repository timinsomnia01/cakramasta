import { Head, Link } from '@inertiajs/react';
import { Bookmark, Star } from 'lucide-react';
import { PageHeader } from '@/components/marketplace/page-header';
import { Pagination } from '@/components/marketplace/pagination';
import { ProductThumb } from '@/components/marketplace/product-thumb';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { deleteAction } from '@/lib/actions';
import { crumbs } from '@/lib/crumbs';
import { formatNumber, formatRupiah } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';
import type { Paginated } from '@/types';

type ProductCard = {
    id: number;
    name: string;
    price: number;
    hasVariants: boolean;
    image: string | null;
    rating: number | null;
    reviewsCount: number;
    soldCount: number;
    category: { name: string };
    storeName: string | null;
    href: string;
};

export default function SavedProducts({ products }: { products: Paginated<ProductCard> }) {
    return (
        <>
            <Head title="Produk Tersimpan" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Produk tersimpan" description="Produk yang kamu simpan untuk dibeli nanti." />
                {products.data.length === 0 ? (
                    <Card className="items-center gap-2 py-12 text-center text-sm text-muted-foreground">
                        <Bookmark className="size-6" aria-hidden />
                        Belum ada produk tersimpan.
                    </Card>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.data.map((product) => (
                            <Card key={product.id} className="gap-3 overflow-hidden py-0">
                                <Link href={product.href} className="block">
                                    <ProductThumb src={product.image} alt={product.name} className="aspect-square w-full rounded-none border-0" />
                                </Link>
                                <div className="flex flex-1 flex-col gap-1 px-4">
                                    <Link href={product.href} className="line-clamp-2 text-sm font-medium hover:underline">{product.name}</Link>
                                    <p className="font-semibold tabular-nums">
                                        {product.hasVariants && <span className="text-xs font-normal text-muted-foreground">mulai </span>}
                                        {formatRupiah(product.price)}
                                    </p>
                                    <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                        {product.rating !== null && (<><Star className="size-3 fill-amber-400 text-amber-500" aria-hidden />{product.rating} · </>)}
                                        {formatNumber(product.soldCount)} terjual · {product.storeName}
                                    </p>
                                </div>
                                <div className="px-4 pb-4">
                                    <Button variant="outline" size="sm" className="w-full" onClick={() => deleteAction(`/produk/${product.id}/simpan`)}>
                                        Hapus dari simpanan
                                    </Button>
                                </div>
                            </Card>
                        ))}
                    </div>
                )}
                <Pagination page={products} />
            </div>
        </>
    );
}

SavedProducts.layout = crumbs(['Akun saya', urls.account.dashboard], ['Produk tersimpan', urls.account.saved]);

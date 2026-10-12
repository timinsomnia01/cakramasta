import { Link } from '@inertiajs/react';
import { ImageOff, Star } from 'lucide-react';
import { formatNumber, formatRupiah } from '@/lib/format';

export type ProductCardData = {
    id: number;
    name: string;
    slug: string;
    price: number;
    hasVariants: boolean;
    image: string | null;
    rating: number | null;
    reviewsCount: number;
    soldCount: number;
    category: { name: string; slug: string };
    storeName: string | null;
    href: string;
};

export function ProductCard({ product }: { product: ProductCardData }) {
    return (
        <Link
            href={product.href}
            className="group flex h-full min-w-0 flex-col overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md"
        >
            <div className="aspect-square overflow-hidden bg-muted">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        loading="lazy"
                        className="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-muted-foreground">
                        <ImageOff className="size-8" aria-hidden />
                    </div>
                )}
            </div>
            <div className="flex flex-1 flex-col gap-1 p-3">
                <p className="text-xs text-muted-foreground">{product.category.name}</p>
                <h3 className="line-clamp-2 text-sm leading-snug font-medium">{product.name}</h3>
                <p className="mt-auto pt-1 text-base font-semibold">
                    {product.hasVariants && (
                        <span className="mr-1 text-xs font-normal text-muted-foreground">mulai</span>
                    )}
                    {formatRupiah(product.price)}
                </p>
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    {product.rating !== null && (
                        <span className="inline-flex items-center gap-0.5">
                            <Star className="size-3 fill-amber-400 text-amber-400" aria-hidden />
                            {product.rating}
                        </span>
                    )}
                    <span>Terjual {formatNumber(product.soldCount)}</span>
                </div>
                {product.storeName && <p className="truncate text-xs text-muted-foreground">{product.storeName}</p>}
            </div>
        </Link>
    );
}

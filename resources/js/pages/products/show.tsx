import { Head, Link, router } from '@inertiajs/react';
import { Bookmark, Heart, MapPin, ShoppingCart, Star, Store } from 'lucide-react';
import { useMemo, useState } from 'react';
import { ReviewDialog } from '@/components/marketplace/review-dialog';
import { ProductCard, type ProductCardData } from '@/components/store/product-card';
import { StoreLayout } from '@/components/store/store-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { deleteAction, postAction } from '@/lib/actions';
import { formatNumber, formatRupiah, formatShortDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

type Variant = { id: number; name: string; price: number; stock: number; image: string | null };
type Review = { id: number; rating: number; comment: string | null; images: (string | null)[]; userName: string; userAvatar: string | null; createdAt: string };

type Props = {
    product: {
        id: number;
        name: string;
        slug: string;
        sku: string | null;
        description: string;
        condition: string;
        weight: number;
        price: number;
        priceMin: number;
        priceMax: number;
        stock: number;
        status: string;
        isPreview: boolean;
        images: { id: number; url: string | null; isPrimary: boolean }[];
        variants: Variant[];
        category: { name: string; slug: string; parent: { name: string; slug: string } | null };
    };
    stats: { likes: number; saves: number; reviews: number; rating: number | null; sold: number; views: number };
    ratingBreakdown: Record<string, number>;
    seller: { storeName: string; slug: string; description: string | null; logo: string | null; location: string | null; productCount: number; joinedAt: string };
    reviews: Paginated<Review> & { links?: unknown };
    related: ProductCardData[];
    viewer: { liked: boolean; saved: boolean; canBuy: boolean; reviewable: { id: number; orderCode: string; variantName: string | null; url: string }[] } | null;
};

const CONDITION: Record<string, string> = { new: 'Baru', used: 'Bekas' };

function Stars({ value }: { value: number }) {
    return (
        <span className="inline-flex" aria-label={`${value} dari 5`}>
            {[1, 2, 3, 4, 5].map((n) => (
                <Star key={n} className={cn('size-4', n <= Math.round(value) ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/40')} aria-hidden />
            ))}
        </span>
    );
}

export default function ProductShow({ product, stats, ratingBreakdown, seller, reviews, related, viewer }: Props) {
    const [imageIndex, setImageIndex] = useState(0);
    const [variantId, setVariantId] = useState<number | null>(product.variants[0]?.id ?? null);
    const [quantity, setQuantity] = useState(1);

    const variant = product.variants.find((item) => item.id === variantId) ?? null;
    const price = variant?.price ?? product.price;
    const stock = variant?.stock ?? product.stock;
    const gallery = useMemo(() => {
        const base = product.images.filter((image) => image.url);
        return variant?.image ? [{ id: -1, url: variant.image, isPrimary: false }, ...base] : base;
    }, [product.images, variant]);
    const current = gallery[Math.min(imageIndex, gallery.length - 1)];
    const login = !viewer;
    const root = product.category.parent ?? product.category;

    const addToCart = () =>
        postAction('/keranjang', { product_id: product.id, product_variant_id: variantId, quantity });
    const toggle = (kind: 'suka' | 'simpan', active: boolean) => {
        const url = `/produk/${product.id}/${kind}`;
        active ? deleteAction(url) : postAction(url);
    };

    const totalReviews = Object.values(ratingBreakdown).reduce((sum, count) => sum + count, 0);

    return (
        <StoreLayout>
            <Head title={product.name} />
            <nav className="mb-4 text-sm text-muted-foreground" aria-label="Breadcrumb">
                <Link href="/" className="hover:text-foreground">Beranda</Link> /{' '}
                <Link href={`/kategori/${root.slug}`} className="hover:text-foreground">{root.name}</Link>
                {product.category.parent && <> / <Link href={`/kategori/${root.slug}?sub=${product.category.slug}`} className="hover:text-foreground">{product.category.name}</Link></>}
            </nav>

            {product.isPreview && (
                <div className="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
                    Pratinjau: produk ini belum disetujui dan belum tampil di katalog.
                </div>
            )}

            <div className="grid gap-8 md:grid-cols-2">
                <div className="min-w-0 space-y-3">
                    <div className="aspect-square overflow-hidden rounded-xl border bg-muted">
                        {current?.url && <img src={current.url} alt={product.name} className="size-full object-cover" />}
                    </div>
                    {gallery.length > 1 && (
                        <div className="flex gap-2 overflow-x-auto pb-1">
                            {gallery.map((image, index) => (
                                <button
                                    key={`${image.id}-${index}`}
                                    type="button"
                                    onClick={() => setImageIndex(index)}
                                    aria-label={`Gambar ${index + 1}`}
                                    className={cn('size-16 shrink-0 overflow-hidden rounded-md border-2', index === imageIndex ? 'border-primary' : 'border-transparent')}
                                >
                                    <img src={image.url ?? ''} alt="" className="size-full object-cover" />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                <div className="min-w-0 space-y-5">
                    <div className="space-y-2">
                        <h1 className="text-2xl leading-tight font-semibold">{product.name}</h1>
                        <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                            {stats.rating !== null && (
                                <span className="inline-flex items-center gap-1">
                                    <Stars value={stats.rating} /> {stats.rating} ({formatNumber(stats.reviews)} ulasan)
                                </span>
                            )}
                            <span>Terjual {formatNumber(stats.sold)}</span>
                            <span>Dilihat {formatNumber(stats.views)}</span>
                        </div>
                    </div>

                    <div>
                        <p className="text-3xl font-bold">{formatRupiah(price)}</p>
                        {!variant && product.priceMin !== product.priceMax && (
                            <p className="text-xs text-muted-foreground">{formatRupiah(product.priceMin)} - {formatRupiah(product.priceMax)}</p>
                        )}
                    </div>

                    {product.variants.length > 0 && (
                        <div className="space-y-2">
                            <p className="text-sm font-medium">Varian</p>
                            <div className="flex flex-wrap gap-2" role="radiogroup" aria-label="Varian">
                                {product.variants.map((item) => (
                                    <button
                                        key={item.id}
                                        type="button"
                                        role="radio"
                                        aria-checked={item.id === variantId}
                                        disabled={item.stock < 1}
                                        onClick={() => { setVariantId(item.id); setQuantity(1); setImageIndex(0); }}
                                        className={cn(
                                            'rounded-md border px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-40',
                                            item.id === variantId ? 'border-primary bg-primary/10 font-medium' : 'hover:bg-muted',
                                        )}
                                    >
                                        {item.name}
                                        {item.stock < 1 && ' (habis)'}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="flex flex-wrap items-center gap-3">
                        <div className="flex items-center gap-2">
                            <label htmlFor="qty" className="text-sm font-medium">Jumlah</label>
                            <Input
                                id="qty"
                                type="number"
                                min={1}
                                max={Math.max(stock, 1)}
                                value={quantity}
                                onChange={(event) => setQuantity(Math.min(Math.max(Number(event.target.value) || 1, 1), Math.max(stock, 1)))}
                                className="w-20"
                            />
                        </div>
                        <span className="text-sm text-muted-foreground">Stok {formatNumber(stock)}</span>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {login ? (
                            <Button asChild size="lg" className="flex-1 sm:flex-none">
                                <Link href="/login"><ShoppingCart aria-hidden /> Masuk untuk membeli</Link>
                            </Button>
                        ) : (
                            <Button size="lg" className="flex-1 sm:flex-none" disabled={!viewer.canBuy || stock < 1} onClick={addToCart}>
                                <ShoppingCart aria-hidden />
                                {stock < 1 ? 'Stok habis' : viewer.canBuy ? 'Tambah ke keranjang' : 'Produk Anda sendiri'}
                            </Button>
                        )}
                        <Button
                            variant="outline"
                            size="lg"
                            aria-pressed={viewer?.liked ?? false}
                            onClick={() => (login ? router.visit('/login') : toggle('suka', viewer.liked))}
                        >
                            <Heart className={cn(viewer?.liked && 'fill-red-500 text-red-500')} aria-hidden /> {formatNumber(stats.likes)}
                        </Button>
                        <Button
                            variant="outline"
                            size="lg"
                            aria-pressed={viewer?.saved ?? false}
                            onClick={() => (login ? router.visit('/login') : toggle('simpan', viewer.saved))}
                        >
                            <Bookmark className={cn(viewer?.saved && 'fill-current')} aria-hidden /> {viewer?.saved ? 'Tersimpan' : 'Simpan'}
                        </Button>
                    </div>

                    <dl className="grid grid-cols-[auto_1fr] gap-x-6 gap-y-1 border-t pt-4 text-sm">
                        <dt className="text-muted-foreground">Kondisi</dt>
                        <dd>{CONDITION[product.condition] ?? product.condition}</dd>
                        <dt className="text-muted-foreground">Berat</dt>
                        <dd>{formatNumber(product.weight)} gram</dd>
                        <dt className="text-muted-foreground">Kategori</dt>
                        <dd>{product.category.name}</dd>
                        {product.sku && (<><dt className="text-muted-foreground">SKU</dt><dd>{product.sku}</dd></>)}
                    </dl>
                </div>
            </div>

            <section className="mt-10 grid gap-6 md:grid-cols-[1fr_20rem]">
                <div className="min-w-0 space-y-3">
                    <h2 className="text-lg font-semibold">Deskripsi</h2>
                    <p className="text-sm leading-relaxed whitespace-pre-line">{product.description}</p>
                </div>
                <div className="flex min-w-0 gap-3 self-start rounded-xl border p-4">
                    <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted">
                        {seller.logo ? <img src={seller.logo} alt="" className="size-full object-cover" /> : <Store className="size-5 text-muted-foreground" aria-hidden />}
                    </div>
                    <div className="min-w-0 space-y-1 text-sm">
                        <p className="font-medium">{seller.storeName}</p>
                        {seller.location && <p className="inline-flex items-center gap-1 text-muted-foreground"><MapPin className="size-3.5" aria-hidden /> {seller.location}</p>}
                        <p className="text-muted-foreground">{formatNumber(seller.productCount)} produk · bergabung {formatShortDate(seller.joinedAt)}</p>
                        {seller.description && <p className="line-clamp-3 text-muted-foreground">{seller.description}</p>}
                    </div>
                </div>
            </section>

            <section className="mt-10 space-y-4">
                <h2 className="text-lg font-semibold">Ulasan ({formatNumber(stats.reviews)})</h2>

                {viewer && viewer.reviewable.length > 0 && (
                    <div className="flex flex-wrap items-center gap-2 rounded-lg border bg-muted/40 p-3 text-sm">
                        <span>Kamu sudah membeli produk ini:</span>
                        {viewer.reviewable.map((item) => (
                            <span key={item.id} className="inline-flex items-center gap-2">
                                <Badge variant="outline">{item.orderCode}{item.variantName ? ` · ${item.variantName}` : ''}</Badge>
                                <ReviewDialog url={item.url} productName={product.name} />
                            </span>
                        ))}
                    </div>
                )}

                {totalReviews > 0 && (
                    <div className="grid gap-4 sm:grid-cols-[10rem_1fr]">
                        <div>
                            <p className="text-4xl font-bold">{stats.rating}</p>
                            {stats.rating !== null && <Stars value={stats.rating} />}
                        </div>
                        <div className="space-y-1">
                            {[5, 4, 3, 2, 1].map((star) => (
                                <div key={star} className="flex items-center gap-2 text-xs">
                                    <span className="w-3 tabular-nums">{star}</span>
                                    <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                                        <div className="h-full rounded-full bg-amber-400" style={{ width: `${(100 * (ratingBreakdown[star] ?? 0)) / totalReviews}%` }} />
                                    </div>
                                    <span className="w-6 text-right tabular-nums text-muted-foreground">{ratingBreakdown[star] ?? 0}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {reviews.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada ulasan.</p>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {reviews.data.map((review) => (
                            <li key={review.id} className="space-y-2 p-4">
                                <div className="flex items-center gap-2">
                                    <div className="flex size-8 items-center justify-center overflow-hidden rounded-full bg-muted text-xs font-medium">
                                        {review.userAvatar ? <img src={review.userAvatar} alt="" className="size-full object-cover" /> : review.userName.slice(0, 1)}
                                    </div>
                                    <div className="text-sm">
                                        <p className="font-medium">{review.userName}</p>
                                        <p className="text-xs text-muted-foreground">{formatShortDate(review.createdAt)}</p>
                                    </div>
                                    <span className="ml-auto"><Stars value={review.rating} /></span>
                                </div>
                                {review.comment && <p className="text-sm">{review.comment}</p>}
                                {review.images.length > 0 && (
                                    <div className="flex gap-2">
                                        {review.images.map((src, index) => src && <img key={index} src={src} alt="Foto ulasan" className="size-16 rounded-md border object-cover" loading="lazy" />)}
                                    </div>
                                )}
                            </li>
                        ))}
                    </ul>
                )}

                {reviews.last_page > 1 && (
                    <div className="flex items-center justify-center gap-3 text-sm">
                        <Button asChild={!!reviews.prev_page_url} variant="outline" size="sm" disabled={!reviews.prev_page_url}>
                            {reviews.prev_page_url ? <Link href={reviews.prev_page_url} preserveScroll>Sebelumnya</Link> : <span>Sebelumnya</span>}
                        </Button>
                        <span className="tabular-nums text-muted-foreground">{reviews.current_page} / {reviews.last_page}</span>
                        <Button asChild={!!reviews.next_page_url} variant="outline" size="sm" disabled={!reviews.next_page_url}>
                            {reviews.next_page_url ? <Link href={reviews.next_page_url} preserveScroll>Berikutnya</Link> : <span>Berikutnya</span>}
                        </Button>
                    </div>
                )}
            </section>

            {related.length > 0 && (
                <section className="mt-10 space-y-3">
                    <h2 className="text-lg font-semibold">Produk serupa</h2>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        {related.map((item) => <ProductCard key={item.id} product={item} />)}
                    </div>
                </section>
            )}
        </StoreLayout>
    );
}

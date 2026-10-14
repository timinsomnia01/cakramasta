import { Head, Link, router } from '@inertiajs/react';
import { Bookmark, ChevronLeft, ChevronRight, Heart, MapPin, ShoppingCart, Star, Store } from 'lucide-react';
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

const decimal = (value: number) => String(value).replace('.', ',');

function Stars({ value, className }: { value: number; className?: string }) {
    return (
        <span className="inline-flex gap-0.5" aria-label={`${value} dari 5`}>
            {[1, 2, 3, 4, 5].map((n) => (
                <Star
                    key={n}
                    className={cn('size-4', n <= Math.round(value) ? 'fill-um-gold text-um-gold' : 'fill-um-grey text-um-grey', className)}
                    aria-hidden
                />
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
    const move = (step: number) => setImageIndex((index) => (index + step + gallery.length) % gallery.length);

    const totalReviews = Object.values(ratingBreakdown).reduce((sum, count) => sum + count, 0);

    return (
        <div className="um-theme min-h-screen bg-background text-foreground">
            <StoreLayout>
                <Head title={product.name} />

                <nav className="mb-6 text-xs text-muted-foreground" aria-label="Breadcrumb">
                    <Link href="/" className="hover:text-um-blue">Beranda</Link> /{' '}
                    <Link href={`/kategori/${root.slug}`} className="hover:text-um-blue">{root.name}</Link>
                    {product.category.parent && (
                        <> / <Link href={`/kategori/${root.slug}?sub=${product.category.slug}`} className="hover:text-um-blue">{product.category.name}</Link></>
                    )}
                </nav>

                {product.isPreview && (
                    <div className="mb-6 rounded-[4px] border border-um-gold bg-um-yellow/25 px-4 py-2 text-sm text-um-navy">
                        Pratinjau: produk ini belum disetujui dan belum tampil di katalog.
                    </div>
                )}

                {/* Galeri + info utama */}
                <div className="grid gap-8 md:grid-cols-2">
                    <div className="min-w-0 space-y-3">
                        <div className="relative aspect-[4/3] overflow-hidden rounded-xl bg-muted">
                            {current?.url && <img src={current.url} alt={product.name} className="size-full object-cover" />}
                            {gallery.length > 1 && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => move(-1)}
                                        aria-label="Gambar sebelumnya"
                                        className="absolute top-1/2 left-3 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-white text-um-blue hover:bg-um-grey"
                                    >
                                        <ChevronLeft className="size-5" aria-hidden />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => move(1)}
                                        aria-label="Gambar berikutnya"
                                        className="absolute top-1/2 right-3 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-white text-um-blue hover:bg-um-grey"
                                    >
                                        <ChevronRight className="size-5" aria-hidden />
                                    </button>
                                </>
                            )}
                        </div>
                        {gallery.length > 1 && (
                            <div className="grid grid-cols-4 gap-3">
                                {gallery.slice(0, 8).map((image, index) => (
                                    <button
                                        key={`${image.id}-${index}`}
                                        type="button"
                                        onClick={() => setImageIndex(index)}
                                        aria-label={`Gambar ${index + 1}`}
                                        aria-current={index === imageIndex}
                                        className={cn(
                                            'aspect-[4/3] overflow-hidden rounded-xl bg-muted outline-2 outline-offset-2',
                                            index === imageIndex ? 'outline outline-um-blue' : 'outline-transparent hover:outline hover:outline-um-grey',
                                        )}
                                    >
                                        <img src={image.url ?? ''} alt="" className="size-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="min-w-0 space-y-5">
                        <div className="space-y-2">
                            <span
                                className={cn(
                                    'inline-block rounded-full px-3 py-0.5 text-xs font-semibold',
                                    stock > 0 ? 'bg-um-blue text-white' : 'bg-um-grey text-um-navy',
                                )}
                            >
                                {stock > 0 ? 'Tersedia' : 'Stok habis'}
                            </span>
                            <h1 className="text-[28px] leading-tight font-bold text-um-navy">{product.name}</h1>
                            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                                {stats.rating !== null && (
                                    <span className="inline-flex items-center gap-1.5">
                                        <Stars value={stats.rating} /> {decimal(stats.rating)} ({formatNumber(stats.reviews)} ulasan)
                                    </span>
                                )}
                                <span>Terjual {formatNumber(stats.sold)}</span>
                                <span>Dilihat {formatNumber(stats.views)}</span>
                            </div>
                        </div>

                        <div>
                            <p className="text-2xl font-bold text-um-blue">{formatRupiah(price)}</p>
                            {!variant && product.priceMin !== product.priceMax && (
                                <p className="text-xs text-muted-foreground">{formatRupiah(product.priceMin)} - {formatRupiah(product.priceMax)}</p>
                            )}
                        </div>

                        {product.variants.length > 0 && (
                            <div className="space-y-2">
                                <p className="text-sm font-semibold">Varian</p>
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
                                                'rounded-[4px] border px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-40',
                                                item.id === variantId
                                                    ? 'border-um-navy bg-um-navy font-medium text-white'
                                                    : 'border-um-grey bg-card hover:border-um-navy',
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
                                <label htmlFor="qty" className="text-sm font-semibold">Jumlah</label>
                                <Input
                                    id="qty"
                                    type="number"
                                    min={1}
                                    max={Math.max(stock, 1)}
                                    value={quantity}
                                    onChange={(event) => setQuantity(Math.min(Math.max(Number(event.target.value) || 1, 1), Math.max(stock, 1)))}
                                    className="w-20 bg-card"
                                />
                            </div>
                            <span className="text-sm text-muted-foreground">Stok {formatNumber(stock)}</span>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {login ? (
                                <Button asChild size="lg" className="flex-1 sm:flex-none">
                                    <Link href="/login">Masuk untuk membeli <ShoppingCart aria-hidden /></Link>
                                </Button>
                            ) : (
                                <Button size="lg" className="flex-1 sm:flex-none" disabled={!viewer.canBuy || stock < 1} onClick={addToCart}>
                                    {stock < 1 ? 'Stok habis' : viewer.canBuy ? 'Tambah ke keranjang' : 'Produk Anda sendiri'}
                                    <ShoppingCart aria-hidden />
                                </Button>
                            )}
                            <Button
                                variant="outline"
                                size="lg"
                                className="border-um-navy bg-transparent text-um-navy hover:bg-um-grey/50"
                                aria-pressed={viewer?.liked ?? false}
                                onClick={() => (login ? router.visit('/login') : toggle('suka', viewer.liked))}
                            >
                                {formatNumber(stats.likes)}
                                <Heart className={cn(viewer?.liked && 'fill-red-500 text-red-500')} aria-hidden />
                            </Button>
                            <Button
                                variant="secondary"
                                size="lg"
                                aria-pressed={viewer?.saved ?? false}
                                onClick={() => (login ? router.visit('/login') : toggle('simpan', viewer.saved))}
                            >
                                {viewer?.saved ? 'Tersimpan' : 'Simpan'}
                                <Bookmark className={cn(viewer?.saved && 'fill-current')} aria-hidden />
                            </Button>
                        </div>

                        <dl className="grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 border-t border-um-grey pt-4 text-sm">
                            <dt className="text-muted-foreground">Kondisi</dt>
                            <dd className="font-medium">{CONDITION[product.condition] ?? product.condition}</dd>
                            <dt className="text-muted-foreground">Berat</dt>
                            <dd className="font-medium">{formatNumber(product.weight)} gram</dd>
                            <dt className="text-muted-foreground">Kategori</dt>
                            <dd className="font-medium">{product.category.name}</dd>
                            {product.sku && (<><dt className="text-muted-foreground">SKU</dt><dd className="font-medium">{product.sku}</dd></>)}
                        </dl>
                    </div>
                </div>

                {/* Deskripsi + penjual */}
                <section className="mt-14 grid gap-6 md:grid-cols-[1fr_20rem]">
                    <div className="min-w-0 space-y-3">
                        <h2 className="um-label">Deskripsi</h2>
                        <p className="text-sm leading-relaxed whitespace-pre-line">{product.description}</p>
                    </div>
                    <div className="flex min-w-0 gap-3 self-start rounded-xl bg-card p-4">
                        <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted">
                            {seller.logo ? <img src={seller.logo} alt="" className="size-full object-cover" /> : <Store className="size-5 text-muted-foreground" aria-hidden />}
                        </div>
                        <div className="min-w-0 space-y-1 text-sm">
                            <p className="font-semibold">{seller.storeName}</p>
                            {seller.location && <p className="inline-flex items-center gap-1 text-muted-foreground"><MapPin className="size-3.5" aria-hidden /> {seller.location}</p>}
                            <p className="text-muted-foreground">{formatNumber(seller.productCount)} produk · bergabung {formatShortDate(seller.joinedAt)}</p>
                            {seller.description && <p className="line-clamp-3 text-muted-foreground">{seller.description}</p>}
                        </div>
                    </div>
                </section>

                {/* Ulasan dan rating */}
                <section className="mt-14 space-y-6">
                    <h2 className="um-label">Ulasan dan Rating ({formatNumber(stats.reviews)})</h2>

                    {totalReviews > 0 && (
                        <div className="flex flex-wrap items-center gap-x-12 gap-y-6">
                            <div>
                                <p className="flex items-end gap-1 leading-none">
                                    <span className="text-7xl font-bold text-um-navy">{stats.rating !== null ? decimal(stats.rating) : '-'}</span>
                                    <span className="pb-2 text-xl font-semibold text-muted-foreground">/5</span>
                                </p>
                                {stats.rating !== null && <div className="mt-2"><Stars value={stats.rating} className="size-5" /></div>}
                            </div>
                            <div className="w-full max-w-xs space-y-1.5">
                                {[5, 4, 3, 2, 1].map((star) => (
                                    <div key={star} className="flex items-center gap-2 text-xs">
                                        <Star className="size-3.5 fill-um-gold text-um-gold" aria-hidden />
                                        <span className="w-3 tabular-nums">{star}</span>
                                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-um-grey">
                                            <div className="h-full rounded-full bg-um-blue" style={{ width: `${(100 * (ratingBreakdown[star] ?? 0)) / totalReviews}%` }} />
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
                        <ul className="grid gap-4 md:grid-cols-2">
                            {reviews.data.map((review) => (
                                <li key={review.id} className="space-y-2 rounded-xl bg-card p-5">
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-9 items-center justify-center overflow-hidden rounded-full bg-muted text-xs font-semibold">
                                            {review.userAvatar ? <img src={review.userAvatar} alt="" className="size-full object-cover" /> : review.userName.slice(0, 1)}
                                        </div>
                                        <div className="text-sm">
                                            <p className="font-semibold">{review.userName}</p>
                                            <p className="text-xs text-muted-foreground">{formatShortDate(review.createdAt)}</p>
                                        </div>
                                    </div>
                                    <Stars value={review.rating} />
                                    {review.comment && <p className="text-sm">{review.comment}</p>}
                                    {review.images.length > 0 && (
                                        <div className="flex gap-2">
                                            {review.images.map((src, index) => src && <img key={index} src={src} alt="Foto ulasan" className="size-16 rounded-[4px] object-cover" loading="lazy" />)}
                                        </div>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}

                    {reviews.last_page > 1 && (
                        <div className="flex items-center justify-center gap-3 text-sm">
                            <Button asChild={!!reviews.prev_page_url} variant="outline" size="sm" className="border-um-navy bg-transparent" disabled={!reviews.prev_page_url}>
                                {reviews.prev_page_url ? <Link href={reviews.prev_page_url} preserveScroll>Sebelumnya</Link> : <span>Sebelumnya</span>}
                            </Button>
                            <span className="tabular-nums text-muted-foreground">{reviews.current_page} / {reviews.last_page}</span>
                            <Button asChild={!!reviews.next_page_url} variant="outline" size="sm" className="border-um-navy bg-transparent" disabled={!reviews.next_page_url}>
                                {reviews.next_page_url ? <Link href={reviews.next_page_url} preserveScroll>Berikutnya</Link> : <span>Berikutnya</span>}
                            </Button>
                        </div>
                    )}

                    {/* Tambah ulasan: tetap lewat ReviewDialog (hanya untuk pembeli) */}
                    {viewer && viewer.reviewable.length > 0 && (
                        <div className="space-y-3 rounded-xl bg-card p-5">
                            <h3 className="text-base font-semibold text-um-navy">Tambah Ulasan</h3>
                            <p className="text-sm text-muted-foreground">Kamu sudah membeli produk ini. Pilih pesanan yang ingin diulas:</p>
                            <div className="flex flex-wrap items-center gap-3">
                                {viewer.reviewable.map((item) => (
                                    <span key={item.id} className="inline-flex items-center gap-2">
                                        <Badge variant="outline" className="rounded-full border-um-blue text-um-blue">
                                            {item.orderCode}{item.variantName ? ` · ${item.variantName}` : ''}
                                        </Badge>
                                        <ReviewDialog url={item.url} productName={product.name} />
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}
                </section>

                {related.length > 0 && (
                    <section className="mt-14 space-y-4">
                        <h2 className="um-label">Produk serupa</h2>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                            {related.map((item) => <ProductCard key={item.id} product={item} />)}
                        </div>
                    </section>
                )}
            </StoreLayout>
        </div>
    );
}
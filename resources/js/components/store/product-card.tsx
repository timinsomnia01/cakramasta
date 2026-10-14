import { Link } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';
import { formatRupiah } from '@/lib/format';

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
    description: string;
};

export function ProductCard({ product }: { product: ProductCardData }) {
    return (
        <Link
            href={product.href}
            className="group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl bg-white transition-shadow hover:shadow-md border border-gray-100"
        >
            {/* Bagian Gambar: Diubah dari aspect-square menjadi aspect-[4/3] agar lebih pendek */}
            <div className="aspect-[4/3] w-full overflow-hidden bg-[#F8F9FA]">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        loading="lazy"
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-muted-foreground">
                        <ImageOff className="size-8" aria-hidden />
                    </div>
                )}
            </div>

            {/* Bagian Konten */}
            <div className="flex flex-1 flex-col p-4">
                {/* Baris Badge: Kategori & Harga */}
                <div className="mb-3 flex flex-wrap items-center justify-end gap-2">
                    <span className="shrink-0 rounded-full border border-[#0ea5e9] px-3 py-0.5 text-[11px] font-semibold text-[#0ea5e9]">
                        {product.category.name}
                    </span>
                    <span className="shrink-0 rounded-full bg-[#D3EDBC] px-3 py-0.5 text-[11px] font-bold text-[#012060]">
                        {formatRupiah(product.price)}
                    </span>
                </div>

                {/* Judul & Teks Dummy */}
                <h3 className="line-clamp-2 text-sm font-bold text-[#012060]">
                    {product.name}
                </h3>
                <p className="mt-2 text-xs leading-relaxed text-neutral-400 line-clamp-2">
                    {product.description}
                </p>
            </div>
        </Link>
    );
}

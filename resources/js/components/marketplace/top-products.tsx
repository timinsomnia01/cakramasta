import { Link } from '@inertiajs/react';

export type TopProductData = {
    id: number;
    name: string;
    slug: string;
    price: number;
    description: string;
    image: string;
};

type Props = {
    products: TopProductData[];
};

export default function TopProducts({ products }: Props) {
    const previewProducts = products.slice(0, 3);

    const formatRupiah = (price: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(price);
    };

    if (previewProducts.length === 0) return null;

    return (
        <section
            // Menghapus left-1/2, w-[100vw], dan -translate-x-1/2.
            // Diganti dengan style calc margin agar parallax (bg-fixed) bisa bekerja.
            className="relative overflow-hidden border-y border-[#cfd4df] py-12 md:py-12"
            style={{ marginLeft: 'calc(50% - 50vw)', marginRight: 'calc(50% - 50vw)' }}
        >

            {/* Background Pattern Parallax */}
            <div
                className="absolute inset-0 z-0 bg-fixed bg-center bg-repeat"
                style={{
                    backgroundImage: "url('/images/pattern.webp')",
                    backgroundSize: '100%',
                    opacity: 0.08,
                }}
                aria-hidden="true"
            />

            {/* Container Konten */}
            <div className="relative z-10 mx-auto max-w-7xl px-4 sm:px-6">
                <div className="flex flex-col gap-6 md:flex-row md:items-center md:gap-8 lg:gap-12">

                    {/* Kolom Kiri: Judul Section */}
                    <div className="shrink-0 md:w-[200px] lg:w-[250px]">
                        <h2 className="text-[#0C8CE9] bg-clip-text text-[22px] font-bold leading-tight md:text-[40px]">
                            Top<br className="hidden md:block" /> Products
                        </h2>
                    </div>

                    {/* Kolom Kanan: List Kartu */}
                    <div className="-mx-4 gap-4 flex snap-x snap-mandatory overflow-x-auto px-4 pb-4 md:mx-0 md:grid md:flex-1 md:grid-cols-3 md:gap-6 md:overflow-visible md:px-0 md:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        {previewProducts.map((product) => (
                            <Link
                                key={product.id}
                                href={`/produk/${product.slug}`}
                                className="group w-[200px] shrink-0 snap-center rounded-[13px] border border-[#cfd4df] bg-white transition-transform duration-300 hover:-translate-y-1 md:w-auto"
                            >
                                <div className="aspect-[4/3] w-full overflow-hidden rounded-t-[13px] bg-[#F8F9FA]">
                                    <img
                                        src={product.image}
                                        alt={product.name}
                                        loading="lazy"
                                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />
                                </div>

                                <div className="flex flex-col p-4">
                                    <div className="mb-2 flex items-start justify-between gap-2">
                                        <h3 className="font-bold text-[#0242C6] line-clamp-1 flex-1">
                                            {product.name}
                                        </h3>
                                        <span className="shrink-0 rounded-full bg-[#D3EDBC] px-2.5 py-1 text-[11px] font-bold text-[#012060]">
                                            {formatRupiah(product.price)}
                                        </span>
                                    </div>

                                    <p className="text-xs leading-relaxed text-neutral-500 line-clamp-3">
                                        {product.description}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>

                </div>
            </div>
        </section>
    );
}

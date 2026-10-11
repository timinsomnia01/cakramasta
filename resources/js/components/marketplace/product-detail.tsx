import { StatusBadge } from '@/components/marketplace/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatNumber, formatRupiah } from '@/lib/format';

export type ProductDetailData = {
    name: string; sku: string | null; price: number; stock: number; status: string; statusLabel: string;
    rejectionReason: string | null; storeName: string | null; description: string; weight: number;
    condition: string; categoryPath: string; images: (string | null)[];
    variants: { name: string; price: number; stock: number; isActive: boolean }[]; verifiedBy: string | null;
};

export function ProductDetailView({ product }: { product: ProductDetailData }) {
    return (
        <div className="grid gap-4 lg:grid-cols-3">
            <div className="flex min-w-0 flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                            {product.name}
                            <StatusBadge status={product.status} label={product.statusLabel} />
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        {product.rejectionReason && (
                            <p className="rounded-md border border-red-600/30 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-950/40 dark:text-red-300">Alasan penolakan: {product.rejectionReason}</p>
                        )}
                        <p className="text-sm whitespace-pre-line">{product.description}</p>
                        <dl className="grid gap-3 text-sm sm:grid-cols-3">
                            {([
                                ['Toko', product.storeName],
                                ['Kategori', product.categoryPath],
                                ['Kondisi', product.condition === 'new' ? 'Baru' : 'Bekas'],
                                ['Berat', `${formatNumber(product.weight)} gram`],
                                ['SKU', product.sku],
                                ['Harga', formatRupiah(product.price)],
                                ['Stok', formatNumber(product.stock)],
                                ['Diverifikasi oleh', product.verifiedBy],
                            ] as const).map(([label, value]) => (
                                <div key={label}>
                                    <dt className="text-xs text-muted-foreground">{label}</dt>
                                    <dd>{value || '—'}</dd>
                                </div>
                            ))}
                        </dl>
                    </CardContent>
                </Card>
                {product.variants.length > 0 && (
                    <Card className="gap-0 py-0">
                        <CardHeader className="border-b py-4"><CardTitle>Varian</CardTitle></CardHeader>
                        <table className="w-full text-sm">
                            <thead><tr className="border-b text-left text-xs text-muted-foreground"><th className="px-6 py-2 font-medium">Nama</th><th className="px-3 py-2 text-right font-medium">Harga</th><th className="px-3 py-2 text-right font-medium">Stok</th><th className="px-6 py-2 font-medium">Status</th></tr></thead>
                            <tbody>
                                {product.variants.map((v) => (
                                    <tr key={v.name} className="border-b last:border-0">
                                        <td className="px-6 py-2">{v.name}</td>
                                        <td className="px-3 py-2 text-right tabular-nums">{formatRupiah(v.price)}</td>
                                        <td className="px-3 py-2 text-right tabular-nums">{formatNumber(v.stock)}</td>
                                        <td className="px-6 py-2">{v.isActive ? 'Aktif' : 'Nonaktif'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </Card>
                )}
            </div>
            <Card className="h-fit">
                <CardHeader><CardTitle>Gambar</CardTitle></CardHeader>
                <CardContent className="grid grid-cols-2 gap-2">
                    {product.images.map((src, i) => src && (
                        <a key={src} href={src} target="_blank" rel="noreferrer" className="block overflow-hidden rounded-md border">
                            <img src={src} alt={`${product.name} ${i + 1}`} className="aspect-square w-full object-cover" loading="lazy" />
                        </a>
                    ))}
                    {product.images.length === 0 && <p className="col-span-2 text-sm text-muted-foreground">Belum ada gambar.</p>}
                </CardContent>
            </Card>
        </div>
    );
}

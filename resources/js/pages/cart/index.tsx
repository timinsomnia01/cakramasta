import { Head, Link, router } from '@inertiajs/react';
import { Minus, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { StoreLayout } from '@/components/store/store-layout';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { deleteAction } from '@/lib/actions';
import { formatRupiah } from '@/lib/format';

export type CartItem = {
    id: number;
    quantity: number;
    unitPrice: number;
    subtotal: number;
    stock: number;
    available: boolean;
    purchasable: boolean;
    product: { id: number; name: string; image: string | null; href: string };
    variant: { id: number; name: string } | null;
};
export type CartGroup = { seller: { id: number; storeName: string; slug: string }; items: CartItem[]; subtotal: number };

export default function CartIndex({ groups, count }: { groups: CartGroup[]; count: number }) {
    const buyable = groups.flatMap((group) => group.items).filter((item) => item.purchasable);
    const [selected, setSelected] = useState<number[]>(() => buyable.map((item) => item.id));

    const toggle = (id: number, checked: boolean) => setSelected((ids) => (checked ? [...ids, id] : ids.filter((value) => value !== id)));
    const setQuantity = (item: CartItem, quantity: number) => {
        if (quantity < 1 || quantity > item.stock) return;
        router.patch(`/keranjang/${item.id}`, { quantity }, { preserveScroll: true });
    };

    const chosen = buyable.filter((item) => selected.includes(item.id));
    const total = chosen.reduce((sum, item) => sum + item.subtotal, 0);
    const checkoutHref = `/checkout?${chosen.map((item) => `items[]=${item.id}`).join('&')}`;

    return (
        <StoreLayout>
            <Head title="Keranjang" />
            <h1 className="mb-4 text-2xl font-semibold">Keranjang ({count})</h1>

            {groups.length === 0 ? (
                <div className="rounded-xl border border-dashed p-10 text-center">
                    <p className="text-sm text-muted-foreground">Keranjangmu masih kosong.</p>
                    <Button asChild className="mt-4"><Link href="/">Mulai belanja</Link></Button>
                </div>
            ) : (
                <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                    <div className="min-w-0 space-y-4">
                        {groups.map((group) => (
                            <section key={group.seller.id} className="overflow-hidden rounded-xl border">
                                <h2 className="border-b bg-muted/40 px-4 py-2 text-sm font-medium">{group.seller.storeName}</h2>
                                <ul className="divide-y">
                                    {group.items.map((item) => (
                                        <li key={item.id} className="flex gap-3 p-4">
                                            <Checkbox
                                                checked={selected.includes(item.id)}
                                                disabled={!item.purchasable}
                                                onCheckedChange={(checked) => toggle(item.id, checked === true)}
                                                aria-label={`Pilih ${item.product.name}`}
                                                className="mt-1"
                                            />
                                            <Link href={item.product.href} className="size-20 shrink-0 overflow-hidden rounded-md bg-muted">
                                                {item.product.image && <img src={item.product.image} alt="" className="size-full object-cover" loading="lazy" />}
                                            </Link>
                                            <div className="min-w-0 flex-1 space-y-1">
                                                <Link href={item.product.href} className="line-clamp-2 text-sm font-medium hover:underline">{item.product.name}</Link>
                                                {item.variant && <p className="text-xs text-muted-foreground">{item.variant.name}</p>}
                                                <p className="text-sm font-semibold">{formatRupiah(item.unitPrice)}</p>
                                                {!item.available && <p className="text-xs text-destructive">Produk tidak tersedia.</p>}
                                                {item.available && !item.purchasable && <p className="text-xs text-destructive">Stok tersisa {item.stock}, kurangi jumlahnya.</p>}
                                                <div className="flex items-center gap-2 pt-1">
                                                    <Button variant="outline" size="icon" className="size-7" onClick={() => setQuantity(item, item.quantity - 1)} disabled={item.quantity <= 1} aria-label="Kurangi">
                                                        <Minus className="size-3" />
                                                    </Button>
                                                    <span className="w-6 text-center text-sm tabular-nums">{item.quantity}</span>
                                                    <Button variant="outline" size="icon" className="size-7" onClick={() => setQuantity(item, item.quantity + 1)} disabled={item.quantity >= item.stock} aria-label="Tambah">
                                                        <Plus className="size-3" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" className="ml-auto size-7 text-destructive" onClick={() => deleteAction(`/keranjang/${item.id}`)} aria-label="Hapus">
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            </section>
                        ))}
                    </div>

                    <aside className="h-fit space-y-3 rounded-xl border p-4 lg:sticky lg:top-20">
                        <h2 className="font-semibold">Ringkasan</h2>
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">{chosen.length} item dipilih</span>
                            <span className="font-semibold">{formatRupiah(total)}</span>
                        </div>
                        <p className="text-xs text-muted-foreground">Ongkir dihitung di langkah berikutnya.</p>
                        <Button asChild={chosen.length > 0} className="w-full" disabled={chosen.length === 0}>
                            {chosen.length > 0 ? <Link href={checkoutHref}>Checkout</Link> : <span>Checkout</span>}
                        </Button>
                    </aside>
                </div>
            )}
        </StoreLayout>
    );
}

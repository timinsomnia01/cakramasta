import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { StoreLayout } from '@/components/store/store-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { formatRupiah } from '@/lib/format';
import type { CartGroup } from '@/pages/cart/index';

type Props = {
    groups: CartGroup[];
    itemIds: number[];
    couriers: { key: string; name: string; cost: number }[];
    paymentMethods: { key: string; label: string }[];
    defaults: { recipient_name: string | null; recipient_phone: string | null; shipping_address: string | null };
    feeRate: number;
};

function Choice({ checked, onChange, children }: { checked: boolean; onChange: () => void; children: React.ReactNode }) {
    return (
        <label className={`flex cursor-pointer items-center gap-3 rounded-lg border p-3 text-sm ${checked ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'}`}>
            <input type="radio" checked={checked} onChange={onChange} className="accent-primary" />
            {children}
        </label>
    );
}

export default function CheckoutCreate({ groups, itemIds, couriers, paymentMethods, defaults }: Props) {
    const form = useForm({
        items: itemIds,
        recipient_name: defaults.recipient_name ?? '',
        recipient_phone: defaults.recipient_phone ?? '',
        shipping_address: defaults.shipping_address ?? '',
        courier: couriers[0]?.key ?? '',
        payment_method: paymentMethods[0]?.key ?? '',
        notes: '',
    });
    const errors = form.errors as Record<string, string | undefined>;
    const courier = couriers.find((item) => item.key === form.data.courier);
    const shipping = (courier?.cost ?? 0) * groups.length;
    const subtotal = groups.reduce((sum, group) => sum + group.subtotal, 0);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/checkout');
    };

    return (
        <StoreLayout>
            <Head title="Checkout" />
            <h1 className="mb-4 text-2xl font-semibold">Checkout</h1>
            <form onSubmit={submit} className="grid gap-6 lg:grid-cols-[1fr_22rem] [&>*]:min-w-0">
                <div className="min-w-0 space-y-6">
                    <section className="space-y-4 rounded-xl border p-4">
                        <h2 className="font-semibold">Alamat pengiriman</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormField label="Nama penerima" htmlFor="name" error={errors.recipient_name}>
                                <Input id="name" value={form.data.recipient_name} onChange={(e) => form.setData('recipient_name', e.target.value)} />
                            </FormField>
                            <FormField label="Nomor HP" htmlFor="phone" error={errors.recipient_phone}>
                                <Input id="phone" inputMode="tel" value={form.data.recipient_phone} onChange={(e) => form.setData('recipient_phone', e.target.value)} placeholder="081234567890" />
                            </FormField>
                        </div>
                        <FormField label="Alamat lengkap" htmlFor="address" error={errors.shipping_address} hint="Jalan, nomor, kecamatan, kota, kode pos.">
                            <Textarea id="address" rows={3} value={form.data.shipping_address} onChange={(e) => form.setData('shipping_address', e.target.value)} />
                        </FormField>
                        <FormField label="Catatan untuk penjual (opsional)" htmlFor="notes" error={errors.notes}>
                            <Input id="notes" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} maxLength={300} />
                        </FormField>
                    </section>

                    <section className="space-y-3 rounded-xl border p-4">
                        <h2 className="font-semibold">Kurir</h2>
                        <div className="grid gap-2 sm:grid-cols-2">
                            {couriers.map((item) => (
                                <Choice key={item.key} checked={form.data.courier === item.key} onChange={() => form.setData('courier', item.key)}>
                                    <span className="flex-1">{item.name}</span>
                                    <span className="text-muted-foreground">{item.cost === 0 ? 'Gratis' : formatRupiah(item.cost)}</span>
                                </Choice>
                            ))}
                        </div>
                        {errors.courier && <p className="text-sm text-destructive">{errors.courier}</p>}
                        <p className="text-xs text-muted-foreground">Ongkir dikenakan per toko.</p>
                    </section>

                    <section className="space-y-3 rounded-xl border p-4">
                        <h2 className="font-semibold">Metode pembayaran</h2>
                        <div className="grid gap-2 sm:grid-cols-2">
                            {paymentMethods.map((item) => (
                                <Choice key={item.key} checked={form.data.payment_method === item.key} onChange={() => form.setData('payment_method', item.key)}>
                                    {item.label}
                                </Choice>
                            ))}
                        </div>
                        {errors.payment_method && <p className="text-sm text-destructive">{errors.payment_method}</p>}
                    </section>
                    {errors.items && <p className="text-sm text-destructive">{errors.items}</p>}
                </div>

                <aside className="h-fit min-w-0 space-y-4 rounded-xl border p-4 lg:sticky lg:top-20">
                    <h2 className="font-semibold">Pesanan</h2>
                    {groups.map((group) => (
                        <div key={group.seller.id} className="space-y-1 text-sm">
                            <p className="font-medium">{group.seller.storeName}</p>
                            {group.items.map((item) => (
                                <div key={item.id} className="flex min-w-0 justify-between gap-2 text-muted-foreground">
                                    <span className="min-w-0 truncate">{item.quantity}× {item.product.name}{item.variant ? ` (${item.variant.name})` : ''}</span>
                                    <span className="shrink-0">{formatRupiah(item.subtotal)}</span>
                                </div>
                            ))}
                        </div>
                    ))}
                    <div className="space-y-1 border-t pt-3 text-sm">
                        <div className="flex justify-between"><span className="text-muted-foreground">Subtotal</span><span>{formatRupiah(subtotal)}</span></div>
                        <div className="flex justify-between"><span className="text-muted-foreground">Ongkir ({groups.length} toko)</span><span>{formatRupiah(shipping)}</span></div>
                        <div className="flex justify-between pt-1 text-base font-semibold"><span>Total</span><span>{formatRupiah(subtotal + shipping)}</span></div>
                    </div>
                    <Button type="submit" className="w-full" disabled={form.processing}>Buat pesanan</Button>
                </aside>
            </form>
        </StoreLayout>
    );
}

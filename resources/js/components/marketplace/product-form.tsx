import { Link, useForm } from '@inertiajs/react';
import { Plus, Star, Trash2, Upload, Undo2 } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { NativeSelect } from '@/components/marketplace/native-select';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { FormProduct, Option } from '@/types';

type Variant = { id?: number; name: string; sku: string; price: string; stock: string; is_active: boolean };

type Props = {
    product: FormProduct | null;
    categories: { id: number; name: string; group: string }[];
    limits: { maxImages: number; maxImageKb: number };
    /** URL simpan: POST untuk produk baru, atau URL update (dikirim sebagai POST + _method=PUT). */
    submitUrl: string;
    cancelUrl: string;
    /** Admin: pilih toko pemilik dan status langsung. */
    admin?: { sellers: { id: number; name: string }[]; statuses: Option[] };
};

export function ProductForm({ product, categories, limits, submitUrl, cancelUrl, admin }: Props) {
    const isEdit = product !== null;
    const form = useForm({
        _method: isEdit ? 'put' : 'post',
        seller_id: String(product?.sellerId ?? admin?.sellers[0]?.id ?? ''),
        category_id: String(product?.category_id ?? ''),
        name: product?.name ?? '',
        sku: product?.sku ?? '',
        description: product?.description ?? '',
        price: String(product?.price ?? ''),
        stock: String(product?.stock ?? ''),
        weight: String(product?.weight ?? ''),
        condition: product?.condition ?? 'new',
        status: admin ? (product?.status ?? 'pending') : '',
        images: [] as File[],
        keep_image_ids: (product?.images ?? []).map((image) => image.id),
        primary_image_id: (product?.images.find((image) => image.isPrimary)?.id ?? null) as number | null,
        images_managed: isEdit,
        variants: (product?.variants ?? []).map(
            (v): Variant => ({ id: v.id, name: v.name, sku: v.sku ?? '', price: String(v.price), stock: String(v.stock), is_active: v.is_active }),
        ),
    });
    const [draft, setDraft] = useState(false);
    const fileInput = useRef<HTMLInputElement>(null);
    const errors = form.errors as Record<string, string | undefined>;
    const hasVariants = form.data.variants.length > 0;

    const previews = useMemo(() => form.data.images.map((file) => URL.createObjectURL(file)), [form.data.images]);
    useEffect(() => () => previews.forEach((url) => URL.revokeObjectURL(url)), [previews]);

    const existing = product?.images ?? [];
    const keptCount = form.data.keep_image_ids.length + form.data.images.length;

    const groups = useMemo(() => {
        const map = new Map<string, typeof categories>();
        categories.forEach((c) => map.set(c.group, [...(map.get(c.group) ?? []), c]));

        return [...map.entries()];
    }, [categories]);

    const updateVariant = (index: number, patch: Partial<Variant>) =>
        form.setData('variants', form.data.variants.map((v, i) => (i === index ? { ...v, ...patch } : v)));

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            status: admin ? data.status : draft ? 'draft' : '',
            primary_image_id: data.primary_image_id ?? '',
            images: data.images,
            // Harga & stok produk diturunkan dari varian saat varian ada.
            price: data.variants.length ? '' : data.price,
            stock: data.variants.length ? '' : data.stock,
        }));
        form.post(submitUrl, { forceFormData: true, preserveScroll: true });
    };

    const addImages = (files: FileList | null) => {
        if (!files) {
            return;
        }

        form.setData('images', [...form.data.images, ...Array.from(files)].slice(0, Math.max(0, limits.maxImages - form.data.keep_image_ids.length)));

        if (fileInput.current) {
            fileInput.current.value = '';
        }
    };

    return (
        <form onSubmit={submit} className="grid gap-4 lg:grid-cols-3">
            <div className="flex min-w-0 flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Informasi produk</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        {product?.rejectionReason && (
                            <p className="rounded-md border border-red-600/30 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-950/40 dark:text-red-300">
                                Ditolak verifikator: {product.rejectionReason}
                            </p>
                        )}
                        <FormField label="Nama produk" htmlFor="name" error={errors.name}>
                            <Input id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} aria-invalid={!!errors.name} />
                        </FormField>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormField label="Kategori" htmlFor="category_id" error={errors.category_id}>
                                <NativeSelect id="category_id" value={form.data.category_id} onChange={(e) => form.setData('category_id', e.target.value)} aria-invalid={!!errors.category_id}>
                                    <option value="">Pilih sub kategori</option>
                                    {groups.map(([group, items]) => (
                                        <optgroup key={group} label={group}>
                                            {items.map((c) => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </optgroup>
                                    ))}
                                </NativeSelect>
                            </FormField>
                            <FormField label="Kondisi" htmlFor="condition" error={errors.condition}>
                                <NativeSelect id="condition" value={form.data.condition} onChange={(e) => form.setData('condition', e.target.value as 'new' | 'used')}>
                                    <option value="new">Baru</option>
                                    <option value="used">Bekas</option>
                                </NativeSelect>
                            </FormField>
                        </div>
                        <FormField label="Deskripsi" htmlFor="description" error={errors.description} hint="Minimal 20 karakter. Jelaskan kondisi, ukuran, dan kelengkapan.">
                            <Textarea id="description" rows={6} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} aria-invalid={!!errors.description} />
                        </FormField>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormField label="SKU (opsional)" htmlFor="sku" error={errors.sku}>
                                <Input id="sku" value={form.data.sku} onChange={(e) => form.setData('sku', e.target.value)} />
                            </FormField>
                            <FormField label="Berat (gram)" htmlFor="weight" error={errors.weight}>
                                <Input id="weight" type="number" min={1} inputMode="numeric" value={form.data.weight} onChange={(e) => form.setData('weight', e.target.value)} aria-invalid={!!errors.weight} />
                            </FormField>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Harga & stok</CardTitle>
                        <CardDescription>
                            {hasVariants ? 'Harga tampil mulai dari varian termurah; stok adalah jumlah semua varian aktif.' : 'Tambah varian bila produk punya pilihan ukuran / warna.'}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        {!hasVariants && (
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField label="Harga (Rp)" htmlFor="price" error={errors.price}>
                                    <Input id="price" type="number" min={100} inputMode="numeric" value={form.data.price} onChange={(e) => form.setData('price', e.target.value)} aria-invalid={!!errors.price} />
                                </FormField>
                                <FormField label="Stok" htmlFor="stock" error={errors.stock}>
                                    <Input id="stock" type="number" min={0} inputMode="numeric" value={form.data.stock} onChange={(e) => form.setData('stock', e.target.value)} aria-invalid={!!errors.stock} />
                                </FormField>
                            </div>
                        )}

                        {form.data.variants.map((variant, index) => (
                            <fieldset key={variant.id ?? `new-${index}`} className="grid gap-3 rounded-lg border p-3 sm:grid-cols-[2fr_1fr_1fr_auto]">
                                <legend className="px-1 text-xs text-muted-foreground">Varian {index + 1}</legend>
                                <FormField label="Nama varian" error={errors[`variants.${index}.name`]}>
                                    <Input value={variant.name} placeholder="Merah / XL" onChange={(e) => updateVariant(index, { name: e.target.value })} />
                                </FormField>
                                <FormField label="Harga (Rp)" error={errors[`variants.${index}.price`]}>
                                    <Input type="number" min={100} value={variant.price} onChange={(e) => updateVariant(index, { price: e.target.value })} />
                                </FormField>
                                <FormField label="Stok" error={errors[`variants.${index}.stock`]}>
                                    <Input type="number" min={0} value={variant.stock} onChange={(e) => updateVariant(index, { stock: e.target.value })} />
                                </FormField>
                                <div className="flex items-end gap-2 pb-1">
                                    <Label className="flex items-center gap-2 text-xs font-normal">
                                        <Checkbox checked={variant.is_active} onCheckedChange={(v) => updateVariant(index, { is_active: v === true })} />
                                        Aktif
                                    </Label>
                                    <Button type="button" variant="ghost" size="icon" aria-label={`Hapus varian ${index + 1}`} onClick={() => form.setData('variants', form.data.variants.filter((_, i) => i !== index))}>
                                        <Trash2 aria-hidden />
                                    </Button>
                                </div>
                            </fieldset>
                        ))}

                        <Button
                            type="button"
                            variant="outline"
                            className="w-fit"
                            onClick={() => form.setData('variants', [...form.data.variants, { name: '', sku: '', price: form.data.price, stock: '', is_active: true }])}
                        >
                            <Plus aria-hidden /> Tambah varian
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div className="flex min-w-0 flex-col gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Gambar</CardTitle>
                        <CardDescription>
                            Maks. {limits.maxImages} gambar, {Math.round(limits.maxImageKb / 1024)} MB per gambar. Gambar pertama menjadi sampul.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        <div className="grid grid-cols-3 gap-2">
                            {existing.map((image) => {
                                const kept = form.data.keep_image_ids.includes(image.id);
                                const primary = form.data.primary_image_id === image.id;

                                return (
                                    <div key={image.id} className="space-y-1">
                                        <div className="relative aspect-square overflow-hidden rounded-md border bg-muted">
                                            {image.url && <img src={image.url} alt="" className={`size-full object-cover ${kept ? '' : 'opacity-30 grayscale'}`} />}
                                            {primary && kept && (
                                                <span className="absolute top-1 left-1 inline-flex items-center gap-0.5 rounded bg-background/90 px-1.5 py-0.5 text-[10px] shadow">
                                                    <Star className="size-3" aria-hidden /> Sampul
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex gap-1">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                className="size-7"
                                                aria-label={kept ? 'Hapus gambar' : 'Batalkan hapus'}
                                                onClick={() =>
                                                    form.setData(
                                                        'keep_image_ids',
                                                        kept ? form.data.keep_image_ids.filter((id) => id !== image.id) : [...form.data.keep_image_ids, image.id],
                                                    )
                                                }
                                            >
                                                {kept ? <Trash2 aria-hidden /> : <Undo2 aria-hidden />}
                                            </Button>
                                            {kept && !primary && (
                                                <Button type="button" variant="outline" size="icon" className="size-7" aria-label="Jadikan sampul" onClick={() => form.setData('primary_image_id', image.id)}>
                                                    <Star aria-hidden />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                            {previews.map((url, index) => (
                                <div key={url} className="space-y-1">
                                    <div className="relative aspect-square overflow-hidden rounded-md border bg-muted">
                                        <img src={url} alt="" className="size-full object-cover" />
                                        <span className="absolute top-1 left-1 rounded bg-background/90 px-1.5 py-0.5 text-[10px] shadow">Baru</span>
                                    </div>
                                    <Button type="button" variant="outline" size="icon" className="size-7" aria-label="Hapus gambar baru" onClick={() => form.setData('images', form.data.images.filter((_, i) => i !== index))}>
                                        <Trash2 aria-hidden />
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <input ref={fileInput} type="file" accept="image/jpeg,image/png,image/webp" multiple hidden onChange={(e) => addImages(e.target.files)} />
                        <Button type="button" variant="outline" disabled={keptCount >= limits.maxImages} onClick={() => fileInput.current?.click()}>
                            <Upload aria-hidden /> Tambah gambar ({keptCount}/{limits.maxImages})
                        </Button>
                        {(errors.images || errors.keep_image_ids) && <p className="text-sm text-red-600 dark:text-red-400">{errors.images ?? errors.keep_image_ids}</p>}
                        {Object.entries(errors).filter(([k]) => k.startsWith('images.')).slice(0, 1).map(([k, m]) => (
                            <p key={k} className="text-sm text-red-600 dark:text-red-400">{m}</p>
                        ))}
                    </CardContent>
                </Card>

                {admin ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Admin</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <FormField label="Toko pemilik" htmlFor="seller_id" error={errors.seller_id}>
                                <NativeSelect id="seller_id" value={form.data.seller_id} onChange={(e) => form.setData('seller_id', e.target.value)}>
                                    {admin.sellers.map((s) => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </NativeSelect>
                            </FormField>
                            <FormField label="Status" htmlFor="status" error={errors.status}>
                                <NativeSelect id="status" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>
                                    {admin.statuses.map((s) => (
                                        <option key={s.value} value={s.value}>{s.label}</option>
                                    ))}
                                </NativeSelect>
                            </FormField>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between gap-2">
                                Status
                                {product && <StatusBadge status={product.status} label={product.statusLabel} />}
                            </CardTitle>
                            <CardDescription>
                                Produk baru atau yang isinya diubah akan ditinjau verifikator sebelum tampil di katalog. Mengubah harga atau stok saja tidak perlu ditinjau ulang.
                            </CardDescription>
                        </CardHeader>
                        {(!product || product.status === 'draft' || product.status === 'rejected') && (
                            <CardContent>
                                <Label className="flex items-center gap-2 font-normal">
                                    <Checkbox checked={draft} onCheckedChange={(v) => setDraft(v === true)} />
                                    Simpan sebagai draft (belum diajukan)
                                </Label>
                            </CardContent>
                        )}
                    </Card>
                )}

                <div className="flex gap-2">
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        {isEdit ? 'Simpan perubahan' : draft ? 'Simpan draft' : admin ? 'Tambah produk' : 'Ajukan produk'}
                    </Button>
                    <Button asChild variant="outline">
                        <Link href={cancelUrl}>Batal</Link>
                    </Button>
                </div>
            </div>
        </form>
    );
}

import { Link, useForm } from '@inertiajs/react';
import { Button, HelperText, Label, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import { route } from 'ziggy-js';

export type Product = {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    price: number;
    stock: number;
};

type Props = {
    slug: string;
    product?: Product; // ada = mode edit, tidak ada = mode tambah
};

export default function ProductForm({ slug, product }: Props) {
    const form = useForm({
        name: product?.name ?? '',
        description: product?.description ?? '',
        price: product ? String(product.price) : '',
        stock: product ? String(product.stock) : '0',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (product) {
            form.put(route('products.update', { current_team: slug, product: product.slug }));
        } else {
            form.post(route('products.store', { current_team: slug }));
        }
    }

    return (
        <form onSubmit={submit} className="flex max-w-xl flex-col gap-4">
            <div>
                <Label htmlFor="name" className="mb-2 block">
                    Nama produk
                </Label>
                <TextInput
                    id="name"
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    color={form.errors.name ? 'failure' : 'gray'}
                />
                {form.errors.name && <HelperText color="failure">{form.errors.name}</HelperText>}
            </div>

            <div>
                <Label htmlFor="description" className="mb-2 block">
                    Deskripsi
                </Label>
                <Textarea
                    id="description"
                    rows={3}
                    value={form.data.description}
                    onChange={(e) => form.setData('description', e.target.value)}
                    color={form.errors.description ? 'failure' : 'gray'}
                />
                {form.errors.description && <HelperText color="failure">{form.errors.description}</HelperText>}
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="price" className="mb-2 block">
                        Harga (Rp)
                    </Label>
                    <TextInput
                        id="price"
                        type="number"
                        min={0}
                        value={form.data.price}
                        onChange={(e) => form.setData('price', e.target.value)}
                        color={form.errors.price ? 'failure' : 'gray'}
                    />
                    {form.errors.price && <HelperText color="failure">{form.errors.price}</HelperText>}
                </div>
                <div>
                    <Label htmlFor="stock" className="mb-2 block">
                        Stok
                    </Label>
                    <TextInput
                        id="stock"
                        type="number"
                        min={0}
                        value={form.data.stock}
                        onChange={(e) => form.setData('stock', e.target.value)}
                        color={form.errors.stock ? 'failure' : 'gray'}
                    />
                    {form.errors.stock && <HelperText color="failure">{form.errors.stock}</HelperText>}
                </div>
            </div>

            <div className="flex items-center gap-3">
                <Button type="submit" color="blue" disabled={form.processing}>
                    {form.processing ? 'Menyimpan...' : 'Simpan'}
                </Button>
                <Button as={Link} color="alternative" href={route('products.index', { current_team: slug })}>
                    Batal
                </Button>
            </div>
        </form>
    );
}

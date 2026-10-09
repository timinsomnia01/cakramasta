import { Link, useForm } from '@inertiajs/react';
import { Button, HelperText, Label, Select, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import { route } from 'ziggy-js';

export type Category = {
    id: number;
    slug: string;
    name: string;
    is_active: boolean | number;
};

type Props = {
    slug: string;
    category?: Category; // ada = mode edit, tidak ada = mode tambah
};

export default function CategoryForm({ slug, category }: Props) {
    const form = useForm({
        name: category?.name ?? '',
        slug: category?.slug ?? '',
        // '1' = Aktif, '0' = Non Aktif. Kategori baru default Aktif.
        is_active: category ? (category.is_active ? '1' : '0') : '1',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (category) {
            form.put(route('categories.update', { current_team: slug, category: category.slug }));
        } else {
            form.post(route('categories.store', { current_team: slug }));
        }
    }

    return (
        <form onSubmit={submit} className="flex max-w-xl flex-col gap-4">
            <div>
                <Label htmlFor="name" className="mb-2 block">
                    Nama kategori
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
                <Label htmlFor="slug" className="mb-2 block">
                    Slug
                </Label>
                <TextInput
                    id="slug"
                    value={form.data.slug}
                    onChange={(e) => form.setData('slug', e.target.value)}
                    color={form.errors.slug ? 'failure' : 'gray'}
                />
                {form.errors.slug && <HelperText color="failure">{form.errors.slug}</HelperText>}
            </div>

            <div>
                <Label htmlFor="is_active" className="mb-2 block">
                    Status
                </Label>
                <Select
                    id="is_active"
                    value={form.data.is_active}
                    onChange={(e) => form.setData('is_active', e.target.value)}
                    color={form.errors.is_active ? 'failure' : 'gray'}
                >
                    <option value="1">Aktif</option>
                    <option value="0">Non Aktif</option>
                </Select>
                {form.errors.is_active && <HelperText color="failure">{form.errors.is_active}</HelperText>}
            </div>

            <div className="flex items-center gap-3">
                <Button type="submit" color="blue" disabled={form.processing}>
                    {form.processing ? 'Menyimpan...' : 'Simpan'}
                </Button>
                <Button as={Link} color="alternative" href={route('categories.index', { current_team: slug })}>
                    Batal
                </Button>
            </div>
        </form>
    );
}

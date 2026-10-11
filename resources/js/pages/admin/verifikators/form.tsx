import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';

type Props = { verifikator: { id: number; name: string; email: string; phone: string | null; isActive: boolean } | null };

export default function VerifikatorForm({ verifikator }: Props) {
    const form = useForm({
        name: verifikator?.name ?? '',
        email: verifikator?.email ?? '',
        phone: verifikator?.phone ?? '',
        password: '',
        password_confirmation: '',
        is_active: verifikator?.isActive ?? true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (verifikator) {
            form.put(`${urls.admin.verifikators}/${verifikator.id}`, { preserveScroll: true });
        } else {
            form.post(urls.admin.verifikators, { preserveScroll: true });
        }
    };

    const field = (key: 'name' | 'email' | 'phone' | 'password' | 'password_confirmation', label: string, type = 'text', hint?: string) => (
        <FormField label={label} htmlFor={key} error={form.errors[key]} hint={hint}>
            <Input id={key} type={type} autoComplete={type === 'password' ? 'new-password' : undefined} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} aria-invalid={!!form.errors[key]} />
        </FormField>
    );

    return (
        <>
            <Head title={verifikator ? 'Ubah Verifikator' : 'Tambah Verifikator'} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title={verifikator ? `Ubah ${verifikator.name}` : 'Tambah verifikator'} />
                <Card className="max-w-xl">
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4">
                            {field('name', 'Nama')}
                            {field('email', 'Email', 'email')}
                            {field('phone', 'Nomor HP (opsional)', 'tel')}
                            {field('password', verifikator ? 'Kata sandi baru' : 'Kata sandi', 'password', verifikator ? 'Kosongkan bila tidak diganti.' : undefined)}
                            {field('password_confirmation', 'Ulangi kata sandi', 'password')}
                            <Label className="flex items-center gap-2 font-normal">
                                <Checkbox checked={form.data.is_active} onCheckedChange={(v) => form.setData('is_active', v === true)} />
                                Akun aktif (bisa mengakses area verifikasi)
                            </Label>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={form.processing}>Simpan</Button>
                                <Button asChild variant="outline"><Link href={urls.admin.verifikators}>Batal</Link></Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

VerifikatorForm.layout = crumbs(['Admin', urls.admin.dashboard], ['Verifikator', urls.admin.verifikators], ['Formulir', '#']);

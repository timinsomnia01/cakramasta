import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { ProtectedImage } from '@/components/marketplace/protected-image';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';

type Props = {
    store: {
        name: string; slug: string; description: string | null; phone: string; address: string; logo: string | null;
        bank_name: string | null; bank_account_number: string | null; bank_account_name: string | null; qrisUrl: string;
    };
};

export default function StoreSettings({ store }: Props) {
    const form = useForm({
        description: store.description ?? '',
        phone: store.phone,
        address: store.address,
        bank_name: store.bank_name ?? '',
        bank_account_number: store.bank_account_number ?? '',
        bank_account_name: store.bank_account_name ?? '',
        logo: null as File | null,
        qris_image: null as File | null,
    });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/toko/pengaturan', { forceFormData: true, preserveScroll: true });
    };

    const field = (key: 'phone' | 'bank_name' | 'bank_account_number' | 'bank_account_name', label: string, inputMode?: 'tel' | 'numeric') => (
        <FormField label={label} htmlFor={key} error={errors[key]}>
            <Input id={key} inputMode={inputMode} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} aria-invalid={!!errors[key]} />
        </FormField>
    );

    return (
        <>
            <Head title="Pengaturan Toko" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Pengaturan toko" description={`${store.name} · nama toko, KTP, dan selfie tidak dapat diubah setelah disetujui.`} />
                <form onSubmit={submit} className="grid max-w-3xl gap-4">
                    <Card>
                        <CardHeader><CardTitle>Profil</CardTitle></CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center gap-4">
                                {store.logo ? <img src={store.logo} alt="Logo toko" className="size-16 rounded-lg border object-cover" /> : <span className="flex size-16 items-center justify-center rounded-lg border bg-muted text-lg font-semibold">{store.name.slice(0, 1)}</span>}
                                <FormField label="Logo toko" htmlFor="logo" error={errors.logo} className="flex-1">
                                    <Input id="logo" type="file" accept="image/*" onChange={(e) => form.setData('logo', e.target.files?.[0] ?? null)} />
                                </FormField>
                            </div>
                            <FormField label="Deskripsi" htmlFor="description" error={errors.description}>
                                <Textarea id="description" rows={3} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                            </FormField>
                            {field('phone', 'Nomor HP', 'tel')}
                            <FormField label="Alamat" htmlFor="address" error={errors.address}>
                                <Textarea id="address" rows={3} value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} aria-invalid={!!errors.address} />
                            </FormField>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Pencairan</CardTitle>
                            <CardDescription>QRIS dan rekening dipakai saat kamu mencairkan saldo.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <p className="text-sm font-medium">QRIS saat ini</p>
                                <ProtectedImage src={store.qrisUrl} alt="QRIS toko" className="h-40" />
                            </div>
                            <FormField label="Ganti QRIS" htmlFor="qris_image" error={errors.qris_image}>
                                <Input id="qris_image" type="file" accept="image/*" onChange={(e) => form.setData('qris_image', e.target.files?.[0] ?? null)} />
                            </FormField>
                            <div className="grid gap-4 sm:grid-cols-3">
                                {field('bank_name', 'Nama bank')}
                                {field('bank_account_number', 'Nomor rekening', 'numeric')}
                                {field('bank_account_name', 'Atas nama')}
                            </div>
                        </CardContent>
                    </Card>
                    <Button type="submit" disabled={form.processing} className="w-fit">Simpan perubahan</Button>
                </form>
            </div>
        </>
    );
}

StoreSettings.layout = crumbs(['Toko', urls.seller.dashboard], ['Pengaturan', '/toko/pengaturan']);

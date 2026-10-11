import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { crumbs } from '@/lib/crumbs';
import { urls } from '@/lib/marketplace-routes';

type Props = {
    profile: { name: string; email: string; phone: string | null; address: string | null; avatar: string | null };
};

export default function PersonalData({ profile }: Props) {
    const form = useForm({
        name: profile.name,
        phone: profile.phone ?? '',
        address: profile.address ?? '',
        avatar: null as File | null,
        remove_avatar: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.account.personalData, { forceFormData: true, preserveScroll: true });
    };

    return (
        <>
            <Head title="Data Diri" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Data diri" description="Dipakai sebagai alamat pengiriman bawaan saat checkout." />
                <Card className="max-w-2xl">
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4">
                            <div className="flex items-center gap-4">
                                {profile.avatar && !form.data.remove_avatar ? (
                                    <img src={profile.avatar} alt="Foto profil" className="size-16 rounded-full border object-cover" />
                                ) : (
                                    <span className="flex size-16 items-center justify-center rounded-full border bg-muted text-lg font-semibold">
                                        {profile.name.slice(0, 1).toUpperCase()}
                                    </span>
                                )}
                                <div className="grid flex-1 gap-1.5">
                                    <Label htmlFor="avatar">Foto profil</Label>
                                    <Input id="avatar" type="file" accept="image/*" onChange={(e) => form.setData('avatar', e.target.files?.[0] ?? null)} />
                                    {profile.avatar && (
                                        <Label className="flex items-center gap-2 text-xs font-normal">
                                            <Checkbox checked={form.data.remove_avatar} onCheckedChange={(v) => form.setData('remove_avatar', v === true)} />
                                            Hapus foto profil
                                        </Label>
                                    )}
                                    {form.errors.avatar && <p className="text-sm text-red-600 dark:text-red-400">{form.errors.avatar}</p>}
                                </div>
                            </div>
                            <FormField label="Nama" htmlFor="name" error={form.errors.name}>
                                <Input id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} aria-invalid={!!form.errors.name} />
                            </FormField>
                            <FormField label="Email" htmlFor="email" hint="Email diubah lewat Pengaturan > Profil.">
                                <Input id="email" value={profile.email} disabled />
                            </FormField>
                            <FormField label="Nomor HP" htmlFor="phone" error={form.errors.phone}>
                                <Input id="phone" inputMode="tel" placeholder="081234567890" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} aria-invalid={!!form.errors.phone} />
                            </FormField>
                            <FormField label="Alamat" htmlFor="address" error={form.errors.address}>
                                <Textarea id="address" rows={3} value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} />
                            </FormField>
                            <Button type="submit" disabled={form.processing} className="w-fit">Simpan</Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

PersonalData.layout = crumbs(['Akun saya', urls.account.dashboard], ['Data diri', urls.account.personalData]);

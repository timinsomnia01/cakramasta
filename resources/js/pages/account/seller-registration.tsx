import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { PageHeader } from '@/components/marketplace/page-header';
import { StatusBadge } from '@/components/marketplace/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { crumbs } from '@/lib/crumbs';
import { formatDateTime } from '@/lib/format';
import { urls } from '@/lib/marketplace-routes';

type Props = {
    seller: { storeName: string; status: string; statusLabel: string; rejectionReason: string | null; submittedAt: string | null } | null;
    form: {
        store_name: string; description: string; phone: string; address: string; ktp_number: string;
        bank_name: string; bank_account_number: string; bank_account_name: string;
    };
    canSubmit: boolean;
    hasDocuments: boolean;
};

export default function SellerRegistration({ seller, form: initial, canSubmit, hasDocuments }: Props) {
    const form = useForm({
        ...initial,
        ktp_image: null as File | null,
        selfie_image: null as File | null,
        qris_image: null as File | null,
        agree: false,
    });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.account.sellerRegistration, { forceFormData: true, preserveScroll: true });
    };

    const text = (key: keyof typeof initial, label: string, extra: { hint?: string; inputMode?: 'numeric' | 'tel'; placeholder?: string } = {}) => (
        <FormField label={label} htmlFor={key} error={errors[key]} hint={extra.hint}>
            <Input id={key} inputMode={extra.inputMode} placeholder={extra.placeholder} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} aria-invalid={!!errors[key]} />
        </FormField>
    );

    const file = (key: 'ktp_image' | 'selfie_image' | 'qris_image', label: string, hint: string) => (
        <FormField label={label} htmlFor={key} error={errors[key]} hint={hasDocuments ? `${hint} Kosongkan untuk memakai berkas yang sudah diunggah.` : hint}>
            <Input id={key} type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => form.setData(key, e.target.files?.[0] ?? null)} aria-invalid={!!errors[key]} />
        </FormField>
    );

    return (
        <>
            <Head title="Daftar Jadi Penjual" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <PageHeader title="Daftar jadi penjual" description="Data dan dokumen diverifikasi tim UM-Mart sebelum tokomu aktif." />

                {seller && (
                    <Card className="max-w-3xl">
                        <CardHeader>
                            <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                                {seller.storeName}
                                <StatusBadge status={seller.status} label={seller.statusLabel} />
                            </CardTitle>
                            {seller.submittedAt && <CardDescription>Terakhir diajukan {formatDateTime(seller.submittedAt)}</CardDescription>}
                        </CardHeader>
                        {seller.rejectionReason && (
                            <CardContent>
                                <p className="rounded-md border border-red-600/30 bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-950/40 dark:text-red-300">
                                    Ditolak: {seller.rejectionReason}. Perbaiki data di bawah lalu ajukan ulang.
                                </p>
                            </CardContent>
                        )}
                        {seller.status === 'pending' && <CardContent className="text-sm text-muted-foreground">Pendaftaranmu sedang ditinjau. Biasanya selesai dalam 1-2 hari kerja.</CardContent>}
                    </Card>
                )}

                {canSubmit && (
                    <form onSubmit={submit} className="grid max-w-3xl gap-4">
                        <Card>
                            <CardHeader><CardTitle>Data toko</CardTitle></CardHeader>
                            <CardContent className="grid gap-4">
                                {text('store_name', 'Nama toko')}
                                <FormField label="Deskripsi toko" htmlFor="description" error={errors.description}>
                                    <Textarea id="description" rows={3} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                                </FormField>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    {text('phone', 'Nomor HP', { inputMode: 'tel', placeholder: '081234567890' })}
                                    {text('ktp_number', 'Nomor KTP', { inputMode: 'numeric', hint: '16 digit.' })}
                                </div>
                                <FormField label="Alamat" htmlFor="address" error={errors.address}>
                                    <Textarea id="address" rows={3} value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} aria-invalid={!!errors.address} />
                                </FormField>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Dokumen</CardTitle>
                                <CardDescription>Disimpan privat dan hanya dilihat tim verifikasi.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                {file('ktp_image', 'Foto KTP', 'Pastikan seluruh tulisan terbaca jelas.')}
                                {file('selfie_image', 'Foto selfie sambil memegang KTP', 'Wajah dan KTP terlihat jelas.')}
                                {file('qris_image', 'Gambar QRIS toko', 'Dipakai untuk pencairan saldo.')}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Rekening bank (opsional)</CardTitle>
                                <CardDescription>Alternatif QRIS untuk pencairan saldo.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-3">
                                {text('bank_name', 'Nama bank', { placeholder: 'BCA' })}
                                {text('bank_account_number', 'Nomor rekening', { inputMode: 'numeric' })}
                                {text('bank_account_name', 'Atas nama')}
                            </CardContent>
                        </Card>

                        <div className="grid gap-2">
                            <Label className="flex items-start gap-2 font-normal">
                                <Checkbox checked={form.data.agree} onCheckedChange={(v) => form.setData('agree', v === true)} className="mt-0.5" />
                                Saya menyetujui peraturan penjual dan kesepakatan pajak UM-Mart.
                            </Label>
                            {errors.agree && <p className="text-sm text-red-600 dark:text-red-400">{errors.agree}</p>}
                        </div>
                        <Button type="submit" disabled={form.processing} className="w-fit">
                            {seller ? 'Ajukan ulang' : 'Kirim pendaftaran'}
                        </Button>
                    </form>
                )}
            </div>
        </>
    );
}

SellerRegistration.layout = crumbs(['Akun saya', urls.account.dashboard], ['Daftar penjual', urls.account.sellerRegistration]);

import { router } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';

type Common = {
    trigger: ReactNode;
    title: string;
    description?: string;
    confirmLabel: string;
    url: string;
    destructive?: boolean;
};

/** Konfirmasi aksi tunggal (POST atau DELETE). Galat dari server tampil sebagai toast. */
export function ConfirmAction({ trigger, title, description, confirmLabel, url, destructive, method = 'post' }: Common & { method?: 'post' | 'delete' }) {
    const [open, setOpen] = useState(false);
    const [busy, setBusy] = useState(false);

    const run = () => {
        const options = {
            preserveScroll: true,
            onStart: () => setBusy(true),
            onFinish: () => setBusy(false),
            onSuccess: () => setOpen(false),
            onError: (errors: Record<string, string>) => {
                setOpen(false);
                const first = Object.values(errors)[0];

                if (first) {
                    toast.error(String(first));
                }
            },
        };

        if (method === 'delete') {
            router.delete(url, options);
        } else {
            router.post(url, {}, options);
        }
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                    <Button variant={destructive ? 'destructive' : 'default'} onClick={run} disabled={busy}>
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

/** Aksi yang wajib disertai alasan (tolak, nonaktifkan, batalkan). Mengirim { [field]: alasan }. */
export function ReasonAction({
    trigger,
    title,
    description,
    confirmLabel,
    url,
    field = 'reason',
    label = 'Alasan',
    required = true,
    destructive = true,
}: Common & { field?: string; label?: string; required?: boolean }) {
    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [error, setError] = useState<string>();
    const [busy, setBusy] = useState(false);

    const run = () =>
        router.post(url, { [field]: reason }, {
            preserveScroll: true,
            onStart: () => setBusy(true),
            onFinish: () => setBusy(false),
            onSuccess: () => {
                setOpen(false);
                setReason('');
                setError(undefined);
            },
            onError: (errors) => setError(Object.values(errors)[0]),
        });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>
                <div className="grid gap-1.5">
                    <Label htmlFor={`${field}-input`}>{label}</Label>
                    <Textarea id={`${field}-input`} value={reason} onChange={(e) => setReason(e.target.value)} rows={4} aria-invalid={!!error} />
                    <InputError message={error} />
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                    <Button variant={destructive ? 'destructive' : 'default'} onClick={run} disabled={busy || (required && reason.trim().length < 1)}>
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

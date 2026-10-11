import { useForm } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/marketplace/form-field';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

export function ReviewDialog({ url, productName }: { url: string; productName: string }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ rating: 0, comment: '', images: [] as File[] });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, { forceFormData: true, preserveScroll: true, onSuccess: () => setOpen(false) });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    <Star aria-hidden /> Beri ulasan
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Ulas produk</DialogTitle>
                        <DialogDescription>{productName}</DialogDescription>
                    </DialogHeader>
                    <FormField label="Penilaian" error={errors.rating}>
                        <div className="flex gap-1" role="radiogroup" aria-label="Penilaian bintang">
                            {[1, 2, 3, 4, 5].map((value) => (
                                <button
                                    key={value}
                                    type="button"
                                    role="radio"
                                    aria-checked={form.data.rating === value}
                                    aria-label={`${value} bintang`}
                                    onClick={() => form.setData('rating', value)}
                                    className="rounded p-0.5 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                >
                                    <Star className={cn('size-7', value <= form.data.rating ? 'fill-amber-400 text-amber-500' : 'text-muted-foreground')} aria-hidden />
                                </button>
                            ))}
                        </div>
                    </FormField>
                    <FormField label="Ulasan (opsional)" htmlFor="comment" error={errors.comment}>
                        <Textarea id="comment" rows={4} value={form.data.comment} onChange={(e) => form.setData('comment', e.target.value)} />
                    </FormField>
                    <FormField label="Foto (maks. 3)" htmlFor="images" error={errors.images ?? errors['images.0']}>
                        <Input id="images" type="file" accept="image/*" multiple onChange={(e) => form.setData('images', Array.from(e.target.files ?? []).slice(0, 3))} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                        <Button type="submit" disabled={form.processing || form.data.rating === 0}>Kirim ulasan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

import { router } from '@inertiajs/react';
import type { VisitOptions } from '@inertiajs/core';
import { toast } from 'sonner';

type Payload = Record<string, string | number | boolean | null | File | undefined>;

function withErrorToast(options: VisitOptions = {}): VisitOptions {
    return {
        preserveScroll: true,
        ...options,
        onError: (errors) => {
            const first = Object.values(errors)[0];

            if (first) {
                toast.error(String(first));
            }

            options.onError?.(errors);
        },
    };
}

/** POST tanpa form (aksi tombol). Pesan galat pertama dari server tampil sebagai toast. */
export function postAction(url: string, data: Payload = {}, options?: VisitOptions) {
    router.post(url, data, withErrorToast(options));
}

export function deleteAction(url: string, options?: VisitOptions) {
    router.delete(url, withErrorToast(options));
}

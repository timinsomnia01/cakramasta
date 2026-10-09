import { usePage } from '@inertiajs/react';
import { Toast, ToastToggle } from 'flowbite-react';
import { Check, X } from 'lucide-react';
import { useEffect, useState } from 'react';

type Flash = { success?: string | null; error?: string | null };

export default function FlashToast() {
    const { flash } = usePage<{ flash?: Flash }>().props;
    const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

    // Jalan setiap kali server mengirim flash baru (setelah redirect)
    useEffect(() => {
        if (flash?.success) {
            setToast({ type: 'success', message: flash.success });
        } else if (flash?.error) {
            setToast({ type: 'error', message: flash.error });
        } else {
            return;
        }

        const timer = setTimeout(() => setToast(null), 4000);
        return () => clearTimeout(timer);
    }, [flash]);

    if (!toast) return null;

    const isSuccess = toast.type === 'success';

    return (
        <div className="fixed top-4 right-4 z-50">
            <Toast>
                <div
                    className={`inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${
                        isSuccess
                            ? 'bg-green-100 text-green-500 dark:bg-green-800 dark:text-green-200'
                            : 'bg-red-100 text-red-500 dark:bg-red-800 dark:text-red-200'
                    }`}
                >
                    {isSuccess ? <Check className="h-5 w-5" /> : <X className="h-5 w-5" />}
                </div>
                <div className="ml-3 text-sm font-normal">{toast.message}</div>
                <ToastToggle onDismiss={() => setToast(null)} />
            </Toast>
        </div>
    );
}

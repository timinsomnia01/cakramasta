import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

type Value = string | number | null | undefined;

/**
 * Filter daftar lewat query string (server yang memfilter). Kolom pencarian ditunda 400 ms,
 * pilihan lain langsung dikirim. Halaman kembali ke 1 karena query berubah.
 */
export function useFilters<T extends Record<string, Value>>(url: string, initial: T) {
    const [values, setValues] = useState<Record<string, string>>(() =>
        Object.fromEntries(Object.entries(initial).map(([k, v]) => [k, v == null ? '' : String(v)])),
    );
    const timer = useRef<number | undefined>(undefined);

    useEffect(() => () => window.clearTimeout(timer.current), []);

    const visit = (next: Record<string, string>) => {
        const query = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== ''));
        router.get(url, query, { preserveState: true, preserveScroll: true, replace: true });
    };

    const set = (key: string, value: string, debounce = false) => {
        const next = { ...values, [key]: value };
        setValues(next);
        window.clearTimeout(timer.current);

        if (debounce) {
            timer.current = window.setTimeout(() => visit(next), 400);
        } else {
            visit(next);
        }
    };

    const reset = () => {
        const cleared = Object.fromEntries(Object.keys(values).map((k) => [k, '']));
        setValues(cleared);
        visit(cleared);
    };

    return { values, set, reset, active: Object.values(values).some((v) => v !== '') };
}

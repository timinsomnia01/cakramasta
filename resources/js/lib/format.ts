const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const number = new Intl.NumberFormat('id-ID');

export const formatRupiah = (value: number): string => rupiah.format(value);

export const formatNumber = (value: number): string => number.format(value);

/** Rupiah ringkas untuk kartu: 1,2 jt / 3,4 M. */
export function formatRupiahCompact(value: number): string {
    const abs = Math.abs(value);

    if (abs >= 1_000_000_000) {
        return `Rp${(value / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`;
    }

    if (abs >= 1_000_000) {
        return `Rp${(value / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`;
    }

    return formatRupiah(value);
}

export function formatDateTime(iso: string): string {
    return new Date(iso).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function formatShortDate(iso: string): string {
    return new Date(`${iso.slice(0, 10)}T00:00:00`).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
}

import { ExternalLink } from 'lucide-react';

/** Gambar dari berkas privat (KTP, selfie, bukti bayar). Klik untuk membuka ukuran penuh di tab baru. */
export function ProtectedImage({ src, alt, className = 'h-48' }: { src: string | null; alt: string; className?: string }) {
    if (!src) {
        return <p className="text-sm text-muted-foreground">Belum ada berkas.</p>;
    }

    return (
        <a href={src} target="_blank" rel="noreferrer" className="group relative block w-fit overflow-hidden rounded-lg border bg-muted">
            <img src={src} alt={alt} className={`${className} w-auto max-w-full object-contain`} loading="lazy" />
            <span className="absolute right-2 bottom-2 inline-flex items-center gap-1 rounded bg-background/90 px-2 py-1 text-xs opacity-0 shadow transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                <ExternalLink className="size-3" aria-hidden /> Buka
            </span>
        </a>
    );
}

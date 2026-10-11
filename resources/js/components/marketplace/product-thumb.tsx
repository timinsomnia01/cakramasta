import { ImageOff } from 'lucide-react';

export function ProductThumb({ src, alt, className = 'size-10' }: { src: string | null; alt: string; className?: string }) {
    return src ? (
        <img src={src} alt={alt} className={`${className} shrink-0 rounded-md border object-cover`} loading="lazy" />
    ) : (
        <span className={`${className} flex shrink-0 items-center justify-center rounded-md border bg-muted text-muted-foreground`}>
            <ImageOff className="size-4" aria-hidden />
        </span>
    );
}

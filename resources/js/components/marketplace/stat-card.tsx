import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type Props = {
    label: string;
    value: string;
    hint?: string;
    icon?: LucideIcon;
    href?: string;
    /** Soroti kartu bila ada yang perlu ditindak (mis. antrean verifikasi tidak kosong). */
    attention?: boolean;
    className?: string;
};

export function StatCard({
    label,
    value,
    hint,
    icon: Icon,
    href,
    attention,
    className,
}: Props) {
    const body = (
        <Card
            className={cn(
                'gap-0 py-4 transition-colors',
                href && 'hover:bg-accent/50',
                attention && 'border-amber-500/50',
                className,
            )}
        >
            <CardContent className="flex items-start justify-between gap-3 px-4">
                <div className="min-w-0">
                    <p className="text-sm text-muted-foreground">{label}</p>
                    <p className="mt-1 truncate text-2xl font-semibold tabular-nums">
                        {value}
                    </p>
                    {hint && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            {hint}
                        </p>
                    )}
                </div>
                {Icon && (
                    <Icon
                        aria-hidden
                        className={cn(
                            'mt-1 size-5 shrink-0 text-muted-foreground',
                            attention && 'text-amber-600 dark:text-amber-400',
                        )}
                    />
                )}
            </CardContent>
        </Card>
    );

    return href ? (
        <Link href={href} prefetch className="block rounded-xl">
            {body}
        </Link>
    ) : (
        body
    );
}

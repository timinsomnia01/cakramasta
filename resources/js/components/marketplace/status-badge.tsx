import { AlertCircle, CheckCircle2, Clock, Truck, XCircle } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type Tone = 'good' | 'wait' | 'bad' | 'info' | 'neutral';

const TONES: Record<Tone, { className: string; icon: LucideIcon | null }> = {
    good: {
        className:
            'border-emerald-600/30 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
        icon: CheckCircle2,
    },
    wait: {
        className:
            'border-amber-600/30 bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
        icon: Clock,
    },
    bad: {
        className:
            'border-red-600/30 bg-red-50 text-red-800 dark:bg-red-950/40 dark:text-red-300',
        icon: XCircle,
    },
    info: {
        className:
            'border-blue-600/30 bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300',
        icon: Truck,
    },
    neutral: { className: '', icon: AlertCircle },
};

const STATUS_TONE: Record<string, Tone> = {
    completed: 'good',
    approved: 'good',
    verified: 'good',
    paid: 'good',
    pending_payment: 'wait',
    waiting_verification: 'wait',
    pending: 'wait',
    processing: 'info',
    shipped: 'info',
    cancelled: 'bad',
    rejected: 'bad',
    suspended: 'bad',
    refunded: 'bad',
    expired: 'bad',
};

/** Status selalu tampil dengan ikon + teks, bukan warna saja. */
export function StatusBadge({
    status,
    label,
    className,
}: {
    status: string;
    label: string;
    className?: string;
}) {
    const tone = TONES[STATUS_TONE[status] ?? 'neutral'];
    const Icon = tone.icon;

    return (
        <Badge variant="outline" className={cn(tone.className, className)}>
            {Icon && <Icon aria-hidden />}
            {label}
        </Badge>
    );
}

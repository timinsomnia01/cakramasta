import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/** <select> bawaan dengan gaya input shadcn: ringan dan mudah dipakai untuk filter / formulir. */
export function NativeSelect({ className, ...props }: ComponentProps<'select'>) {
    return (
        <select
            {...props}
            className={cn(
                'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                'aria-invalid:border-destructive disabled:cursor-not-allowed disabled:opacity-50',
                '[&>option]:bg-popover [&>option]:text-popover-foreground',
                className,
            )}
        />
    );
}

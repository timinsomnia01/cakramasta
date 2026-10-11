import { Search, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { NativeSelect } from '@/components/marketplace/native-select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Option } from '@/types';

type Filters = { values: Record<string, string>; set: (k: string, v: string, d?: boolean) => void; reset: () => void; active: boolean };

export function FilterBar({
    filters,
    searchKey = 'q',
    placeholder = 'Cari...',
    statuses,
    statusKey = 'status',
    statusLabel = 'Semua status',
    children,
}: {
    filters: Filters;
    searchKey?: string;
    placeholder?: string;
    statuses?: Option[];
    statusKey?: string;
    statusLabel?: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-center gap-2">
            <div className="relative min-w-52 flex-1">
                <Search aria-hidden className="pointer-events-none absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                <Input
                    type="search"
                    value={filters.values[searchKey] ?? ''}
                    onChange={(e) => filters.set(searchKey, e.target.value, true)}
                    placeholder={placeholder}
                    aria-label={placeholder}
                    className="pl-8"
                />
            </div>
            {statuses && (
                <NativeSelect
                    aria-label="Status"
                    value={filters.values[statusKey] ?? ''}
                    onChange={(e) => filters.set(statusKey, e.target.value)}
                    className="w-auto min-w-40"
                >
                    <option value="">{statusLabel}</option>
                    {statuses.map((s) => (
                        <option key={s.value} value={s.value}>{s.label}</option>
                    ))}
                </NativeSelect>
            )}
            {children}
            {filters.active && (
                <Button type="button" variant="ghost" size="sm" onClick={filters.reset}>
                    <X aria-hidden /> Reset
                </Button>
            )}
        </div>
    );
}

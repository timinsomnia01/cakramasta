import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import type { Paginated } from '@/types';

export function Pagination({ page }: { page: Paginated<unknown> }) {
    if (page.total === 0) {
        return null;
    }

    return (
        <div className="flex flex-wrap items-center justify-between gap-2 border-t px-6 py-3 text-sm text-muted-foreground">
            <span>
                {formatNumber(page.from ?? 0)}-{formatNumber(page.to ?? 0)} dari{' '}
                {formatNumber(page.total)}
            </span>
            {page.last_page > 1 && (
                <div className="flex items-center gap-2">
                    <Button asChild={!!page.prev_page_url} variant="outline" size="sm" disabled={!page.prev_page_url}>
                        {page.prev_page_url ? (
                            <Link href={page.prev_page_url} preserveScroll>Sebelumnya</Link>
                        ) : (
                            <span>Sebelumnya</span>
                        )}
                    </Button>
                    <span className="tabular-nums">
                        {page.current_page} / {page.last_page}
                    </span>
                    <Button asChild={!!page.next_page_url} variant="outline" size="sm" disabled={!page.next_page_url}>
                        {page.next_page_url ? (
                            <Link href={page.next_page_url} preserveScroll>Berikutnya</Link>
                        ) : (
                            <span>Berikutnya</span>
                        )}
                    </Button>
                </div>
            )}
        </div>
    );
}

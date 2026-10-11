import type { ReactNode } from 'react';
import { Pagination } from '@/components/marketplace/pagination';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

export type Column<T> = {
    header: string;
    cell: (row: T) => ReactNode;
    align?: 'right';
    className?: string;
};

export function DataTable<T>({
    columns,
    page,
    rowKey,
    empty = 'Tidak ada data.',
    toolbar,
}: {
    columns: Column<T>[];
    page: Paginated<T>;
    rowKey: (row: T) => string | number;
    empty?: string;
    toolbar?: ReactNode;
}) {
    return (
        <Card className="min-w-0 gap-0 py-0">
            {toolbar && <div className="border-b p-4">{toolbar}</div>}
            {page.data.length === 0 ? (
                <p className="px-6 py-10 text-center text-sm text-muted-foreground">{empty}</p>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-xs text-muted-foreground">
                                {columns.map((column, index) => (
                                    <th
                                        key={column.header}
                                        className={cn(
                                            'px-3 py-2 font-medium first:pl-6 last:pr-6',
                                            column.align === 'right' && 'text-right',
                                            index === 0 && 'pl-6',
                                        )}
                                    >
                                        {column.header}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {page.data.map((row) => (
                                <tr key={rowKey(row)} className="border-b last:border-0 hover:bg-accent/40">
                                    {columns.map((column) => (
                                        <td
                                            key={column.header}
                                            className={cn(
                                                'px-3 py-3 align-middle first:pl-6 last:pr-6',
                                                column.align === 'right' && 'text-right tabular-nums',
                                                column.className,
                                            )}
                                        >
                                            {column.cell(row)}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            <Pagination page={page} />
        </Card>
    );
}

import type { BreadcrumbItem } from '@/types';

/** Layout halaman Inertia: hanya breadcrumb, selebihnya memakai AppLayout default. */
export const crumbs = (...items: [string, string][]) => ({
    breadcrumbs: items.map(([title, href]): BreadcrumbItem => ({ title, href })),
});

import { Head } from '@inertiajs/react';
import { StoreLayout } from '@/components/store/store-layout';
import { formatDateTime } from '@/lib/format';

export default function StaticPage({ page }: { page: { title: string; slug: string; content: string; updatedAt: string } }) {
    return (
        <StoreLayout>
            <Head title={page.title} />
            <article className="mx-auto max-w-3xl">
                <h1 className="text-3xl font-semibold">{page.title}</h1>
                <p className="mt-1 mb-6 text-xs text-muted-foreground">Diperbarui {formatDateTime(page.updatedAt)}</p>
                {/* Konten HTML dari admin (halaman statis), bukan input pengguna umum. */}
                <div
                    className="space-y-3 text-sm leading-relaxed [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mt-4 [&_h3]:font-semibold [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5 [&_a]:underline"
                    dangerouslySetInnerHTML={{ __html: page.content }}
                />
            </article>
        </StoreLayout>
    );
}

import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Badge,
    Button,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Pagination,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import { useState } from 'react';
import { route } from 'ziggy-js';
import FlashToast from '@/components/flash-toast';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/categories';
import type { BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    slug: string;
    name: string;
    is_active: boolean;
};

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

type Props = {
    categories: Paginated<Category>;
};

const statusBadge = {
    active: { label: 'Aktif', color: 'success' },
    inactive: { label: 'Non Aktif', color: 'failure' },
} as const;

export default function CategoriesIndex({ categories }: Props) {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;
    const slug = currentTeam.slug;

    const [toDelete, setToDelete] = useState<Category | null>(null);
    const [deleting, setDeleting] = useState(false);

    function goToPage(page: number) {
        router.get(
            route('categories.index', { current_team: slug }),
            { page },
            { preserveScroll: true, preserveState: true },
        );
    }

    function confirmDelete() {
        if (!toDelete) return;
        router.delete(route('categories.destroy', { current_team: slug, product: toDelete.slug }), {
            preserveScroll: true,
            onStart: () => setDeleting(true),
            onFinish: () => {
                setDeleting(false);
                setToDelete(null);
            },
        });
    }

    return (
        <>
            <Head title="Categories" />
            <FlashToast />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Categories</h1>
                        <p className="text-sm text-muted-foreground">Kelola daftar kategori produk di sini.</p>
                    </div>
                    <Button as={Link} color="blue" href={route('categories.create', { current_team: slug })}>
                        Tambah kategori
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    {categories.data.length === 0 ? (
                        <div className="p-10 text-center">
                            <p className="font-medium">Belum ada kategori</p>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Klik "Tambah kategori" untuk membuat produk pertama.
                            </p>
                        </div>
                    ) : (
                        <Table hoverable>
                            <TableHead>
                                <TableRow>
                                    <TableHeadCell>Nama</TableHeadCell>
                                    <TableHeadCell>Slug</TableHeadCell>
                                    <TableHeadCell>Status</TableHeadCell>
                                    <TableHeadCell className="text-right">Aksi</TableHeadCell>
                                </TableRow>
                            </TableHead>
                            <TableBody className='divide-y'>
                                {categories.data.map((category) => {
                                    const badge = category.is_active ? statusBadge.active : statusBadge.inactive;
                                    return (
                                        <TableRow key={category.id} className="bg-white dark:bg-gray-800">
                                            <TableCell className="font-medium text-gray-900 dark:text-white">
                                                {category.name}
                                            </TableCell>
                                            <TableCell className="tabular-nums">
                                                {category.slug}
                                            </TableCell>
                                            <TableCell>
                                                <Badge color={badge.color} className='w-fit'>
                                                    {badge.label}
                                                </Badge>

                                            </TableCell>
                                            <TableCell>
                                                <div className="flex justify-end gap-2">
                                                    <Button
                                                        as={Link}
                                                        size="xs"
                                                        color="alternative"
                                                        href={route('categories.edit', {
                                                            current_team: slug,
                                                            category: category.slug,
                                                        })}
                                                    >
                                                        Edit
                                                    </Button>
                                                    <Button size="xs" color="red" onClick={() => setToDelete(category)}>
                                                        Hapus
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    )}
                </div>

                {categories.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-2 sm:flex-row">
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {categories.from}-{categories.to} dari {categories.total} produk
                        </p>
                        <Pagination
                            currentPage={categories.current_page}
                            totalPages={categories.last_page}
                            onPageChange={goToPage}
                            previousLabel="Sebelumnya"
                            nextLabel="Berikutnya"
                        />
                    </div>
                )}
            </div>

            <Modal show={toDelete !== null} size="md" onClose={() => setToDelete(null)}>
                <ModalHeader>Hapus produk</ModalHeader>
                <ModalBody>
                    <p className="text-gray-600 dark:text-gray-300">
                        Kategori <span className="font-semibold">{toDelete?.name}</span> akan dihapus dan tidak bisa
                        dikembalikan.
                    </p>
                </ModalBody>
                <ModalFooter>
                    <Button color="red" onClick={confirmDelete} disabled={deleting}>
                        {deleting ? 'Menghapus...' : 'Ya, hapus'}
                    </Button>
                    <Button color="alternative" onClick={() => setToDelete(null)}>
                        Batal
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}

CategoriesIndex.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
        {
            title: 'Categories',
            href: props.currentTeam ? categoriesIndex(props.currentTeam.slug) : '/categories',
        },
    ] as BreadcrumbItem[],
});

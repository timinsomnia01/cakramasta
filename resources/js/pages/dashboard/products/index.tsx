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
import { index as productsIndex } from '@/routes/products';
import type { BreadcrumbItem } from '@/types';

type Product = {
    id: number;
    slug: string;
    name: string;
    status: 'approved' | 'pending' | 'rejected';
    price: number;
    stock: number;
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
    products: Paginated<Product>;
};

const statusBadge: Record<Product['status'], { label: string; color: 'success' | 'warning' | 'failure' }> = {
    approved: { label: 'Approved', color: 'success' },
    pending: { label: 'Pending', color: 'warning' },
    rejected: { label: 'Rejected', color: 'failure' },
};

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

export default function ProductsIndex({ products }: Props) {
    const { currentTeam } = usePage<{ currentTeam: { slug: string } }>().props;
    const slug = currentTeam.slug;

    const [toDelete, setToDelete] = useState<Product | null>(null);
    const [deleting, setDeleting] = useState(false);

    function goToPage(page: number) {
        router.get(
            route('products.index', { current_team: slug }),
            { page },
            { preserveScroll: true, preserveState: true },
        );
    }

    function confirmDelete() {
        if (!toDelete) return;
        router.delete(route('products.destroy', { current_team: slug, product: toDelete.slug }), {
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
            <Head title="Products" />
            <FlashToast />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Products</h1>
                        <p className="text-sm text-muted-foreground">Kelola daftar produk di sini.</p>
                    </div>
                    <Button as={Link} color="blue" href={route('products.create', { current_team: slug })}>
                        Tambah produk
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    {products.data.length === 0 ? (
                        <div className="p-10 text-center">
                            <p className="font-medium">Belum ada produk</p>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Klik "Tambah produk" untuk membuat produk pertama.
                            </p>
                        </div>
                    ) : (
                        <Table hoverable>
                            <TableHead>
                                <TableRow>
                                    <TableHeadCell>Nama</TableHeadCell>
                                    <TableHeadCell>Status</TableHeadCell>
                                    <TableHeadCell className="text-right">Harga</TableHeadCell>
                                    <TableHeadCell className="text-right">Stok</TableHeadCell>
                                    <TableHeadCell className="text-right">Aksi</TableHeadCell>
                                </TableRow>
                            </TableHead>
                            <TableBody className="divide-y">
                                {products.data.map((product) => {
                                    const badge = statusBadge[product.status];
                                    return (
                                        <TableRow key={product.id} className="bg-white dark:bg-gray-800">
                                            <TableCell className="font-medium text-gray-900 dark:text-white">
                                                {product.name}
                                            </TableCell>
                                            <TableCell>
                                                <Badge color={badge?.color ?? 'gray'} className="w-fit">
                                                    {badge?.label ?? product.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {rupiah.format(product.price)}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {product.stock === 0 ? (
                                                    <span className="font-medium text-red-600">Habis</span>
                                                ) : (
                                                    product.stock
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex justify-end gap-2">
                                                    <Button
                                                        as={Link}
                                                        size="xs"
                                                        color="alternative"
                                                        href={route('products.edit', {
                                                            current_team: slug,
                                                            product: product.slug,
                                                        })}
                                                    >
                                                        Edit
                                                    </Button>
                                                    <Button size="xs" color="red" onClick={() => setToDelete(product)}>
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

                {products.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-2 sm:flex-row">
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {products.from}-{products.to} dari {products.total} produk
                        </p>
                        <Pagination
                            currentPage={products.current_page}
                            totalPages={products.last_page}
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
                        Produk <span className="font-semibold">{toDelete?.name}</span> akan dihapus dan tidak bisa
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

ProductsIndex.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
        {
            title: 'Products',
            href: props.currentTeam ? productsIndex(props.currentTeam.slug) : '/products',
        },
    ] as BreadcrumbItem[],
});

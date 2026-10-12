import { Link, usePage } from '@inertiajs/react';
import { Search, ShoppingCart, UserCircle } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

/**
 * Kerangka halaman publik (beranda, katalog, detail produk, keranjang).
 * Dipakai langsung oleh halaman, bukan lewat AppLayout bersidebar.
 */
export function StoreLayout({ children, initialQuery = '' }: { children: ReactNode; initialQuery?: string }) {
    const { auth, name, marketplace } = usePage<{
        auth: { user: { name: string } | null };
        name: string;
        marketplace: { cartCount: number; footerPages: { title: string; href: string }[] };
    }>().props;
    const [query, setQuery] = useState(initialQuery);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        window.location.assign(`/cari?q=${encodeURIComponent(query)}`);
    };

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="sticky top-0 z-30 border-b bg-background/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center gap-3 px-4 py-3">
                    <Link href="/" className="shrink-0 text-lg font-bold tracking-tight">
                        {name ?? 'UM-Mart'}
                    </Link>

                    <form onSubmit={submit} role="search" className="relative mx-auto hidden max-w-xl flex-1 sm:block">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden />
                        <Input
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Cari produk di kampus..."
                            aria-label="Cari produk"
                            className="pl-9"
                        />
                    </form>

                    <nav className="ml-auto flex items-center gap-1 sm:ml-0">
                        <Button variant="ghost" size="icon" asChild className="relative">
                            <Link href="/keranjang" aria-label="Keranjang">
                                <ShoppingCart className="size-5" />
                                {marketplace.cartCount > 0 && (
                                    <span className="absolute -top-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-medium text-primary-foreground">
                                        {marketplace.cartCount > 9 ? '9+' : marketplace.cartCount}
                                    </span>
                                )}
                            </Link>
                        </Button>
                        {auth?.user ? (
                            <Button variant="ghost" size="sm" asChild>
                                <Link href="/dashboard">
                                    <UserCircle className="size-4" /> <span className="hidden sm:inline">Akun</span>
                                </Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href="/login">Masuk</Link>
                                </Button>
                                <Button size="sm" asChild>
                                    <Link href="/register">Daftar</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
                <form onSubmit={submit} role="search" className="px-4 pb-3 sm:hidden">
                    <Input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Cari produk di kampus..."
                        aria-label="Cari produk"
                    />
                </form>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-6">{children}</main>

            <footer className="border-t bg-muted/40">
                <div className="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm sm:flex-row sm:justify-between">
                    <div>
                        <p className="font-semibold">{name ?? 'UM-Mart'}</p>
                        <p className="text-muted-foreground">Marketplace untuk civitas kampus.</p>
                    </div>
                    <ul className="flex flex-wrap gap-x-6 gap-y-2">
                        {marketplace.footerPages.map((page) => (
                            <li key={page.href}>
                                <Link href={page.href} className="text-muted-foreground hover:text-foreground">
                                    {page.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </footer>
        </div>
    );
}

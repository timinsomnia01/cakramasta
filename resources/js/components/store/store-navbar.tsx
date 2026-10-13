import { Link, usePage } from '@inertiajs/react';
import { Search, ShoppingCart, UserCircle, X } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Button } from '@/components/ui/button';

type StoreNavbarProps = {
    initialQuery?: string;
};

export function StoreNavbar({ initialQuery = '' }: StoreNavbarProps) {
    const { auth, marketplace } = usePage<{
        auth: { user: { name: string } | null };
        marketplace: { cartCount: number };
    }>().props;

    const [query, setQuery] = useState(initialQuery);
    const [authOpen, setAuthOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        window.location.assign(`/cari?q=${encodeURIComponent(query)}`);
    };

    // Tutup dropdown saat klik di luar
    useEffect(() => {
        const handler = (e: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
                setAuthOpen(false);
            }
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, []);

    return (
        <header className="sticky top-0 z-30 bg-[#012060] shadow-md">
            <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
                {/* Brand */}
                <Link
                    href="/"
                    className="flex shrink-0 items-center gap-3 transition-opacity hover:opacity-90"
                >
                    <img
                        src="/images/Logo UM.webp"
                        alt="Logo UM"
                        className="size-16 object-contain"
                    />
                    <div className="h-8 w-px bg-white/40" />
                    <div className="flex flex-col leading-tight">
                        <span className="text-[16px] font-bold text-white sm:text-[22px]">
                            {'Cakramasta'}
                        </span>
                        <span className="text-[12px] text-white/75 sm:text-[16px]">
                            Universitas Negeri Malang
                        </span>
                    </div>
                </Link>

                {/* Search – desktop */}
                <form
                    onSubmit={submit}
                    role="search"
                    className="relative mx-4 hidden flex-1 sm:block"
                >
                    <input
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Search for product..."
                        aria-label="Cari produk"
                        className="w-full rounded-full border border-white/50 bg-transparent py-2 pl-5 pr-11 text-sm text-white placeholder:text-white/65 focus:border-white focus:outline-none focus:ring-1 focus:ring-white"
                    />
                    <button
                        type="submit"
                        aria-label="Cari"
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 transition hover:text-white"
                    >
                        <Search className="size-4" />
                    </button>
                </form>

                {/* Nav: Cart + Auth */}
                <nav className="ml-auto flex shrink-0 items-center gap-1 sm:ml-0">
                    {/* Cart */}
                    <Button
                        variant="ghost"
                        size="icon"
                        asChild
                        className="relative text-white hover:bg-white/10 hover:text-white"
                    >
                        <Link href="/keranjang" aria-label="Keranjang">
                            <ShoppingCart className="size-5" />
                            {marketplace?.cartCount > 0 && (
                                <span className="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-medium text-white">
                                    {marketplace.cartCount > 9 ? '9+' : marketplace.cartCount}
                                </span>
                            )}
                        </Link>
                    </Button>

                    {auth?.user ? (
                        /* Sudah login: tampilkan Akun */
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="text-white hover:bg-white/10 hover:text-white"
                        >
                            <Link href="/dashboard">
                                <UserCircle className="size-4" />
                                <span className="ml-1 hidden sm:inline">Akun</span>
                            </Link>
                        </Button>
                    ) : (
                        <>
                            {/* Desktop: Masuk + Daftar inline */}
                            <Button
                                variant="ghost"
                                size="sm"
                                asChild
                                className="hidden text-white hover:bg-white/10 hover:text-white sm:flex"
                            >
                                <Link href="/login">Masuk</Link>
                            </Button>
                            <Button
                                size="sm"
                                asChild
                                className="hidden bg-white text-[#012060] hover:bg-white/90 sm:flex"
                            >
                                <Link href="/register">Daftar</Link>
                            </Button>

                            {/* Mobile: icon → dropdown */}
                            <div ref={dropdownRef} className="relative sm:hidden">
                                <button
                                    onClick={() => setAuthOpen((v) => !v)}
                                    aria-label="Menu akun"
                                    className="flex size-9 items-center justify-center rounded-full text-white transition hover:bg-white/10"
                                >
                                    {authOpen ? (
                                        <X className="size-5" />
                                    ) : (
                                        <UserCircle className="size-5" />
                                    )}
                                </button>

                                {/* Dropdown panel */}
                                {authOpen && (
                                    <div className="absolute right-0 top-full mt-2 w-40 overflow-hidden rounded-xl border border-white/20 bg-[#012060] shadow-xl">
                                        <Link
                                            href="/login"
                                            onClick={() => setAuthOpen(false)}
                                            className="flex items-center gap-2 px-4 py-3 text-sm text-white transition hover:bg-white/10"
                                        >
                                            Masuk
                                        </Link>
                                        <div className="mx-4 h-px bg-white/10" />
                                        <Link
                                            href="/register"
                                            onClick={() => setAuthOpen(false)}
                                            className="flex items-center gap-2 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                        >
                                            Daftar
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </nav>
            </div>

            {/* Search – mobile */}
            <form onSubmit={submit} role="search" className="px-4 pb-3 sm:hidden">
                <div className="relative">
                    <input
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Cari produk di kampus..."
                        aria-label="Cari produk"
                        className="w-full rounded-full border border-white/50 bg-transparent py-2 pl-5 pr-10 text-sm text-white placeholder:text-white/65 focus:border-white focus:outline-none focus:ring-1 focus:ring-white"
                    />
                    <button
                        type="submit"
                        aria-label="Cari"
                        className="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/70 hover:text-white"
                    >
                        <Search className="size-4" />
                    </button>
                </div>
            </form>
        </header>
    );
}

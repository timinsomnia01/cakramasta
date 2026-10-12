import { Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';

type NavbarProps = {
    initialSearch?: string;
};

export default function Navbar({ initialSearch = '' }: NavbarProps) {
    const [searchQuery, setSearchQuery] = useState(initialSearch);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        const trimmed = searchQuery.trim();
        if (trimmed) {
            router.get('/cari', { q: trimmed });
        } else {
            router.get('/cari');
        }
    };

    return (
        <header className="w-full bg-[#012060] shadow-sm">
            <div className="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                {/* Brand / Logo */}
                <Link href="/" className="flex items-center gap-3 transition-opacity hover:opacity-95">
                    <img
                        src="/images/Logo UM.png"
                        alt="Logo Universitas Negeri Malang"
                        className="size-10 object-contain sm:size-11"
                    />
                    <div className="h-8 w-px bg-white/40" />
                    <div className="flex flex-col">
                        <span className="text-[16px] font-bold leading-tight tracking-tight text-white sm:text-[22px]">
                            Cakramasta
                        </span>
                        <span className="text-[12px] font-normal leading-tight text-white/80 sm:text-[16px]">
                            Universitas Negeri Malang
                        </span>
                    </div>
                </Link>

                {/* Search Bar */}
                <form onSubmit={handleSearch} className="relative w-64 sm:w-80 md:w-96 lg:w-[480px]">
                    <input
                        type="text"
                        name="q"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        placeholder="Search for products..."
                        className="w-full rounded-full border border-white/50 bg-transparent py-2 pr-11 pl-5 text-sm text-white placeholder:text-white/70 transition-colors focus:border-white focus:ring-1 focus:ring-white focus:outline-none"
                    />
                    <button
                        type="submit"
                        aria-label="Cari produk"
                        className="absolute top-1/2 right-3.5 -translate-y-1/2 text-white/80 transition-colors hover:text-white"
                    >
                        <Search className="size-4.5" />
                    </button>
                </form>
            </div>
        </header>
    );
}


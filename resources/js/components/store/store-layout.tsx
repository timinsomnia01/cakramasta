import { type ReactNode } from 'react';
import { StoreNavbar } from './store-navbar';
import { UmFooter } from './um-footer';

/**
 * Kerangka halaman publik (beranda, katalog, detail produk, keranjang).
 * Dipakai langsung oleh halaman, bukan lewat AppLayout bersidebar.
 */
export function StoreLayout({ children, initialQuery = '' }: { children: ReactNode; initialQuery?: string }) {
    return (
        <div className="flex min-h-screen flex-col bg-[#F8F9FA] text-neutral-900">
            {/* ── Navbar Komponen Terpisah ───────────────────── */}
            <StoreNavbar initialQuery={initialQuery} />

            {/* ── Content ─────────────────────────────────────────── */}
            <main className="mx-auto w-[90%] max-w-[2000px] flex-1 px-4 py-6">{children}</main>

            {/* ── Footer ──────────────────────────────────────────── */}
            <UmFooter />
        </div>
    );
}

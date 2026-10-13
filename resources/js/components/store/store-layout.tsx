import { type ReactNode } from 'react';
import { StoreNavbar } from './store-navbar';
import { UmFooter } from './um-footer';

/**
 * Kerangka halaman publik (beranda, katalog, detail produk, keranjang).
 * Dipakai langsung oleh halaman, bukan lewat AppLayout bersidebar.
 */
export function StoreLayout({ children, initialQuery = '' }: { children: ReactNode; initialQuery?: string }) {
    return (
        <div className="flex min-h-screen flex-col overflow-x-hidden bg-[#F8F9FA] text-neutral-900">
            {/* ── Navbar (Fixed & Sticky) ──────────────────────────── */}
            <StoreNavbar initialQuery={initialQuery} />

            {/* ── Spacer Responsif:
                h-32 untuk HP (2 baris navbar),
                sm:h-20 untuk Desktop (1 baris navbar, agar jaraknya pas & tidak terlalu renggang) ── */}
            <div className="h-32 sm:h-20 shrink-0" aria-hidden="true" />

            {/* ── Content ─────────────────────────────────────────── */}
            <main className="mx-auto w-[90%] max-w-[2000px] flex-1 py-6">{children}</main>

            {/* ── Footer ──────────────────────────────────────────── */}
            <UmFooter />
        </div>
    );
}

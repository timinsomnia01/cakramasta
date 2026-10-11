import { Link, usePage } from '@inertiajs/react';
import {
    Banknote,
    CreditCard,
    Globe,
    Heart,
    IdCard,
    LayoutGrid,
    Package,
    ReceiptText,
    Settings,
    ShieldCheck,
    ShoppingBag,
    Store,
    UserCircle,
    UserCog,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urls } from '@/lib/marketplace-routes';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const page = usePage();
    const { role, isSeller, sellerStatus }: any = page.props.marketplace;
    const teamDashboard = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/';

    // Satu "pintu masuk" per area yang boleh diakses peran ini.
    const home =
        role === 'admin'
            ? urls.admin.dashboard
            : role === 'verifikator'
              ? urls.verification.dashboard
              : urls.account.dashboard;

    const groups: { label: string; items: NavItem[] }[] = [];

    if (role === 'admin') {
        groups.push({
            label: 'Admin',
            items: [
                { title: 'Dashboard', href: urls.admin.dashboard, icon: LayoutGrid },
                { title: 'Verifikator', href: urls.admin.verifikators, icon: UserCog },
                { title: 'Produk', href: urls.admin.products, icon: Package },
                { title: 'Penjual', href: urls.admin.sellers, icon: Store },
                { title: 'Pembelian', href: urls.admin.orders, icon: ReceiptText },
            ],
        });
    }

    if (role === 'admin' || role === 'verifikator') {
        groups.push({
            label: 'Verifikasi',
            items: [
                { title: 'Dashboard', href: urls.verification.dashboard, icon: ShieldCheck },
                { title: 'Penjual', href: urls.verification.sellers, icon: Store },
                { title: 'Produk', href: urls.verification.products, icon: Package },
                { title: 'Pembayaran', href: urls.verification.payments, icon: CreditCard },
                { title: 'Pencairan', href: urls.verification.withdrawals, icon: Banknote },
            ],
        });
    }

    const accountItems: NavItem[] = [
        { title: 'Dashboard', href: urls.account.dashboard, icon: UserCircle },
        { title: 'Pesanan', href: urls.account.orders, icon: ShoppingBag },
        { title: 'Tersimpan', href: urls.account.saved, icon: Heart },
        { title: 'Data diri', href: urls.account.personalData, icon: IdCard },
    ];
    if (!isSeller && sellerStatus === null) {
        accountItems.push({
            title: 'Daftar penjual',
            href: urls.account.sellerRegistration,
            icon: Store,
        });
    }
    groups.push({ label: 'Akun', items: accountItems });

    if (isSeller) {
        groups.push({
            label: 'Toko',
            items: [
                { title: 'Dashboard', href: urls.seller.dashboard, icon: Store },
                { title: 'Produk', href: urls.seller.products, icon: Package },
                { title: 'Pesanan', href: urls.seller.orders, icon: ReceiptText },
                { title: 'Pencairan', href: urls.seller.withdrawals, icon: Banknote },
                { title: 'Pengaturan', href: urls.seller.settings, icon: Settings },
            ],
        });
    }

    groups.push({
        label: 'Tim',
        items: [{ title: 'Dashboard Tim', href: teamDashboard, icon: LayoutGrid }],
    });

    const footerNavItems: NavItem[] = [
        { title: 'Lihat Toko Online', href: '/', icon: Globe },
        { title: 'Keranjang', href: '/keranjang', icon: ShoppingBag },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {groups.map((group) => (
                    <NavMain key={group.label} label={group.label} items={group.items} />
                ))}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

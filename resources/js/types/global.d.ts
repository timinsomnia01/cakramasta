import type { Auth } from '@/types/auth';
import type { MarketplaceShared } from '@/types/marketplace';
import type { Team } from '@/types/teams';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            currentTeam: Team | null;
            teams: Team[];
            marketplace: MarketplaceShared;
            [key: string]: unknown;
        };
    }
}

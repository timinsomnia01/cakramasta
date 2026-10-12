export type UserRole = 'user' | 'verifikator' | 'admin';

export type MarketplaceShared = {
    role: UserRole | null;
    isSeller: boolean;
    sellerStatus: 'pending' | 'approved' | 'rejected' | 'suspended' | null;
    cartCount: number;
    footerPages: { title: string; slug: string; href: string }[];
};

export type OrderRow = {
    code: string;
    status: string;
    statusLabel: string;
    total: number;
    sellerIncome: number;
    createdAt: string;
    buyerName: string | null;
    storeName: string | null;
    itemsCount: number;
    items: {
        name: string;
        variant: string | null;
        quantity: number;
        image: string | null;
    }[];
    href: string;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type Option = { value: string; label: string };

export type Payment = {
    id: number;
    method: string;
    methodLabel: string;
    status: string;
    statusLabel: string;
    amount: number;
    expiresAt: string | null;
    isExpired: boolean;
    rejectionReason: string | null;
    hasProof: boolean;
    proofUrl: string | null;
};

export type OrderDetailData = {
    code: string;
    status: string;
    statusLabel: string;
    subtotal: number;
    shippingCost: number;
    platformFee: number;
    taxAmount: number;
    total: number;
    sellerIncome: number;
    recipient: { name: string; phone: string; address: string };
    courier: string;
    trackingNumber: string | null;
    notes: string | null;
    cancelReason: string | null;
    createdAt: string;
    paidAt: string | null;
    shippedAt: string | null;
    completedAt: string | null;
    cancelledAt: string | null;
    buyer: { name: string; email: string; phone: string | null } | null;
    seller: { storeName: string; slug: string } | null;
    items: {
        id: number;
        productName: string;
        variantName: string | null;
        image: string | null;
        price: number;
        quantity: number;
        subtotal: number;
        reviewed: boolean | null;
    }[];
    logs: {
        status: string;
        statusLabel: string;
        note: string | null;
        by: string | null;
        createdAt: string;
    }[];
    payment: Payment | null;
};

export type ProductRow = {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    price: number;
    stock: number;
    status: string;
    statusLabel: string;
    rejectionReason: string | null;
    image: string | null;
    category: string | null;
    storeName: string | null;
    viewCount: number;
    soldCount: number;
    createdAt: string | null;
};

export type SellerRow = {
    id: number;
    storeName: string;
    slug: string;
    ownerName: string | null;
    ownerEmail: string | null;
    phone: string;
    status: string;
    statusLabel: string;
    productsCount: number | null;
    ordersCount: number | null;
    createdAt: string;
};

export type SellerDetail = SellerRow & {
    description: string | null;
    address: string;
    ktpNumber: string;
    bank: { name: string; number: string; holder: string } | null;
    documents: { ktp: string; selfie: string; qris: string };
    logo: string | null;
    rejectionReason: string | null;
    verifiedBy: string | null;
    verifiedAt: string | null;
};

export type WithdrawalRow = {
    code: string;
    amount: number;
    method: string;
    methodLabel: string;
    status: string;
    statusLabel: string;
    destination: string;
    bank: { name: string; number: string; holder: string } | null;
    qrisUrl: string | null;
    notes: string | null;
    rejectionReason: string | null;
    hasTransferProof: boolean;
    transferProofUrl: string | null;
    storeName: string | null;
    processedBy: string | null;
    processedAt: string | null;
    createdAt: string;
};

export type FormProduct = {
    id: number;
    sellerId: number;
    name: string;
    sku: string | null;
    category_id: number;
    description: string;
    price: number;
    stock: number;
    weight: number;
    condition: 'new' | 'used';
    status: string;
    statusLabel: string;
    rejectionReason: string | null;
    images: { id: number; url: string | null; isPrimary: boolean }[];
    variants: {
        id: number;
        name: string;
        sku: string | null;
        price: number;
        stock: number;
        is_active: boolean;
    }[];
};

export type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    productCount: number;
    href: string;
};

export type ProductCard = {
    id: number;
    name: string;
    slug: string;
    price: number;
    hasVariants: boolean;
    image: string | null;
    rating: number | null;
    reviewsCount: number;
    soldCount: number;
    category: {
        name: string;
        slug: string;
    };
    storeName: string | null;
    href: string;
};


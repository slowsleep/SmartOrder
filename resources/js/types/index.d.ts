import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    login: string;
    first_name: string;
    last_name: string;
    patronymic: string;
    birth_date: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export type TableType = {
    id: number;
    number: number;
    qr_token: string;
    status: string;
}

export type ProductType = {
    id: number;
    name: string;
    description: string | null;
    price: number;
    quantity: number;
    image: string | null;
};

export type OrderType = {
    id: number;
    table_id: number;
    status: string;
    guest_token: string;
    expires_at: string | null;
    paid_at: string | null;
    notes: string | null;
    created_at: string;
    items: OrderItemType[];
    table: TableType;
};

export type OrderItemType = {
    id: number;
    order_id: number;
    product_id: number;
    unit_price: number;
    status: string;
    cook_id: number | null;
    waiter_id: number | null;
    served_at: string | null;
    notes: string | null;
    created_at: string;
    product: ProductType;
    order: OrderType;
};

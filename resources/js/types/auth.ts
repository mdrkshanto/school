export type User = {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    roles?: string[];
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    permissions: string[];
};

export type Flash = {
    success?: { message: string; data?: unknown } | null;
    error?: { message: string } | null;
    warning?: { message: string } | null;
};
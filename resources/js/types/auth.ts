export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: 'admin' | 'support';
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

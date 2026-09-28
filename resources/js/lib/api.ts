/**
 * Thin fetch wrapper for the JSON API. Public /api/* endpoints are stateless
 * (no CSRF needed); authenticated /api/admin/* endpoints run through the
 * 'web' session guard and do need the XSRF-TOKEN cookie echoed back as a
 * header on mutating requests, which this adds automatically when present.
 */

export type ApiErrorBody = {
    success: false;
    message: string;
    errors?: Record<string, string[]>;
};

export class ApiError extends Error {
    constructor(
        message: string,
        public status: number,
        public errors?: Record<string, string[]>,
    ) {
        super(message);
    }
}

function readCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

async function request<T>(method: string, url: string, body?: unknown): Promise<T> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
    };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const xsrfToken = readCookie('XSRF-TOKEN');
    if (xsrfToken && method !== 'GET') {
        headers['X-XSRF-TOKEN'] = xsrfToken;
    }

    const response = await fetch(url, {
        method,
        headers,
        credentials: 'same-origin',
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    const json = await response.json().catch(() => null);

    if (!response.ok) {
        const errorBody = json as ApiErrorBody | null;
        throw new ApiError(
            errorBody?.message ?? 'Something went wrong. Please try again.',
            response.status,
            errorBody?.errors,
        );
    }

    return json as T;
}

export const api = {
    get: <T>(url: string) => request<T>('GET', url),
    post: <T>(url: string, body?: unknown) => request<T>('POST', url, body),
    patch: <T>(url: string, body?: unknown) => request<T>('PATCH', url, body),
};

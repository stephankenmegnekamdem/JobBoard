function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}
export function apiFetch(
    path: string,
    options: RequestInit = {},
): Promise<Response> {
    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');
    if (options.body) {
        headers.set('Content-Type', 'application/json');
    }
    if (options.method && options.method !== 'GET') {
        headers.set('X-XSRF-TOKEN', xsrfToken());
    }
    return fetch(path, { ...options, headers, credentials: 'same-origin' });
}

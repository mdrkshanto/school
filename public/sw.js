const CACHE_NAME = 'school-v2';
const APP_SHELL = ['/'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL).catch(() => Promise.resolve())),
        self.skipWaiting(),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))),
        ),
        self.clients.claim(),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Speculative prefetch requests can't be satisfied via fetch() and must
    // never fail a real navigation. Let the browser handle them directly.
    if (request.cache === 'only-if-cached') return;

    // Always fetch navigation requests (HTML) from the network,
    // fall back to the cached index when offline (SPA).
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put('/', copy));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match('/').then(
                        (cached) => cached ?? new Response('Offline', { status: 503, statusText: 'Service Unavailable' }),
                    ),
                ),
        );
        return;
    }

    // Static assets: cache-first with network fallback.
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response.ok) {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    })
                    .catch(() => cached);
                return cached || network;
            }),
        );
        return;
    }

    // Everything else (media, icons): cache-first, then network.
    event.respondWith(
        caches.match(request).then(
            (cached) =>
                cached ||
                fetch(request)
                    .then((response) => {
                        if (response.ok && url.pathname.startsWith('/icons/')) {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    })
                    .catch(() => new Response('Offline', { status: 503, statusText: 'Service Unavailable' })),
        ),
    );
});
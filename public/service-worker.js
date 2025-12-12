/* eslint-disable no-restricted-globals */

const STATIC_CACHE = 'cragmont-static-v1';
const PAGE_CACHE = 'cragmont-pages-v1';
const RUNTIME_CACHE = 'cragmont-runtime-v1';

function isViteAsset(url) {
    return url.pathname.startsWith('/build/');
}

function isNavigationRequest(request) {
    return request.mode === 'navigate' || (request.destination === '' && request.headers.get('accept')?.includes('text/html'));
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(STATIC_CACHE);
            await cache.addAll(['/']);
            await self.skipWaiting();
        })(),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();
            const keep = new Set([STATIC_CACHE, PAGE_CACHE, RUNTIME_CACHE]);
            await Promise.all(keys.filter((k) => !keep.has(k)).map((k) => caches.delete(k)));
            await self.clients.claim();
        })(),
    );
});

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response && response.ok) {
        cache.put(request, response.clone());
    }
    return response;
}

async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response && response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await cache.match(request);
        if (cached) return cached;
        const fallback = await caches.open(STATIC_CACHE).then((c) => c.match('/'));
        return fallback || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
}

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (isNavigationRequest(event.request)) {
        event.respondWith(networkFirst(event.request, PAGE_CACHE));
        return;
    }

    if (url.origin === self.location.origin && isViteAsset(url)) {
        event.respondWith(cacheFirst(event.request, STATIC_CACHE));
        return;
    }

    // Cache same-origin images (including topo image routes) and common CDN assets (opaque).
    if (event.request.destination === 'image' || url.origin === self.location.origin || url.hostname === 'cdn.jsdelivr.net') {
        event.respondWith(cacheFirst(event.request, RUNTIME_CACHE));
    }
});


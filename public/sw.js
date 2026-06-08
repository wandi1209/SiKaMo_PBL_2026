const CACHE_VERSION = 'v3';
const APP_SHELL_CACHE = `sikamo-shell-${CACHE_VERSION}`;
const RUNTIME_CACHE = `sikamo-runtime-${CACHE_VERSION}`;
const OFFLINE_POS_URL = '/merchant/sales/catalog';
const CORE_ASSETS = [
    '/',
    '/manifest.json',
    '/assets/logo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(APP_SHELL_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS.map((url) => new Request(url, { credentials: 'same-origin' }))))
            .catch(() => undefined)
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('sikamo-') && ![APP_SHELL_CACHE, RUNTIME_CACHE].includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/livewire/') || url.pathname.startsWith('/merchant/offline-pos/')) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

async function networkFirstNavigation(request) {
    const cache = await caches.open(RUNTIME_CACHE);

    try {
        const response = await fetch(request);

        if (response.ok) {
            cache.put(request, response.clone());

            const url = new URL(request.url);

            // Simpan halaman kasir dengan key canonical juga.
            // Jadi saat user offline dari URL dashboard/tenant, SW tetap bisa fallback ke POS offline.
            if (url.pathname === OFFLINE_POS_URL) {
                cache.put(OFFLINE_POS_URL, response.clone());
            }
        }

        return response;
    } catch (error) {
        const cached = await cache.match(request)
            || await cache.match(OFFLINE_POS_URL)
            || await caches.match(OFFLINE_POS_URL)
            || await cache.match('/');

        if (cached) {
            return cached;
        }

        return new Response('SiKaMo sedang offline dan halaman belum tersimpan di perangkat ini.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
        });
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);
    const fetchPromise = fetch(request)
        .then((response) => {
            if (response.ok) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || fetchPromise;
}

function isStaticAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/assets/')
        || url.pathname.startsWith('/storage/')
        || url.pathname.endsWith('.css')
        || url.pathname.endsWith('.js')
        || url.pathname.endsWith('.svg')
        || url.pathname.endsWith('.png')
        || url.pathname.endsWith('.jpg')
        || url.pathname.endsWith('.jpeg')
        || url.pathname.endsWith('.webp')
        || url.pathname.endsWith('.ico')
        || url.pathname === '/manifest.json';
}

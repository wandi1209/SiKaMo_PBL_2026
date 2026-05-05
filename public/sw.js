const CACHE_NAME = 'sikamo-pwa-v1';

self.addEventListener('install', event => {
    // Perform install steps
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', event => {
    // Minimal fetch event handler to satisfy PWA requirements
    // For a real offline experience, caching strategy would go here
    event.respondWith(
        fetch(event.request).catch(() => {
            return new Response('Offline mode not fully implemented yet.');
        })
    );
});

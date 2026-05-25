const CACHE_NAME = 'minevida-offline-v2';
const OFFLINE_URL = '/offline';
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/images/MineVidaLogo.png',
    '/images/lumo_fondo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.mode !== 'navigate') {
        if (
            request.method === 'GET'
            && url.origin === self.location.origin
            && ['style', 'script', 'image', 'font'].includes(request.destination)
        ) {
            event.respondWith(
                caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                }).catch(() => cached || new Response('', { status: 504, statusText: 'Offline' })))
            );
        }

        return;
    }

    event.respondWith(
        fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
});

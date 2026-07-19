const VERSION = 'listora-sprint1-v1';
const STATIC_CACHE = `${VERSION}-static`;
const PUBLIC_CACHE = `${VERSION}-public`;
const OFFLINE_URL = '/offline';

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        const urls = ['/manifest.webmanifest', '/images/icons/listora-192.png', '/images/icons/listora-512.png'];
        await Promise.all(urls.map(url => fetch(url, { credentials: 'omit' }).then(response => response.ok && cache.put(url, response))));
        const offline = await fetch(OFFLINE_URL, { credentials: 'omit' });
        if (offline.ok) await cache.put(OFFLINE_URL, offline);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter(key => ![STATIC_CACHE, PUBLIC_CACHE].includes(key)).map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('message', event => {
    if (event.data?.type === 'AUTHENTICATED') {
        event.waitUntil(caches.delete(PUBLIC_CACHE).then(() => event.ports[0]?.postMessage({ cleared: true })));
    }
});

const isNetworkOnly = url =>
    url.pathname.startsWith('/auth/') ||
    url.pathname.startsWith('/logout') ||
    url.pathname.startsWith('/api/') ||
    url.pathname.startsWith('/payments') ||
    url.pathname.startsWith('/chat');

const isStaticAsset = url =>
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/images/icons/') ||
    /\.(?:css|js|woff2)$/.test(url.pathname);

const isPublicImage = url =>
    url.pathname.startsWith('/images/properties/') && /-(?:thumb)\.(?:avif|webp|jpe?g)$/.test(url.pathname);

async function cacheFirst(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response.ok) await cache.put(request, response.clone());
    return response;
}

async function stalePublicPage(event) {
    const cache = await caches.open(PUBLIC_CACHE);
    const cached = await cache.match(event.request);
    const update = fetch(event.request).then(async response => {
        if (response.ok && response.headers.get('X-Listora-Private') !== '1') {
            await cache.put(event.request, response.clone());
        } else if (response.headers.get('X-Listora-Private') === '1') {
            await cache.delete(event.request);
        }
        return response;
    });

    if (cached) {
        event.waitUntil(update.catch(() => null));
        return cached;
    }

    try {
        return await update;
    } catch {
        return (await caches.match(OFFLINE_URL)) || Response.error();
    }
}

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin || request.method !== 'GET') return;
    if (isNetworkOnly(url)) {
        event.respondWith(fetch(request));
        return;
    }
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }
    if (isPublicImage(url)) {
        event.respondWith(stalePublicPage(event));
        return;
    }
    if (request.mode === 'navigate' && (url.pathname === '/' || url.pathname === '/saved' || url.pathname.startsWith('/properties'))) {
        event.respondWith(stalePublicPage(event));
    }
});

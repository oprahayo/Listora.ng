const CACHE_PREFIX = 'listora-public';
const VERSION = 'v3';
const STATIC_CACHE = `${CACHE_PREFIX}-${VERSION}-static`;
const PUBLIC_CACHE = `${CACHE_PREFIX}-${VERSION}-public`;
const CURRENT_CACHES = [STATIC_CACHE, PUBLIC_CACHE];
const BASE_PATH = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const appPath = path => `${BASE_PATH}${path}`;
const OFFLINE_URL = appPath(BASE_PATH ? '/offline/' : '/offline');

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        const urls = [appPath('/manifest.webmanifest'), appPath('/images/icons/listora-192.png'), appPath('/images/icons/listora-512.png')];
        await Promise.all(urls.map(url => fetch(url, { cache: 'no-store', credentials: 'omit' }).then(response => response.ok && cache.put(url, response))));
        const offline = await fetch(OFFLINE_URL, { cache: 'no-store', credentials: 'omit' });
        if (offline.ok) await cache.put(OFFLINE_URL, offline);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        const oldListoraCaches = keys.filter(key => key.toLowerCase().startsWith('listora-') && !CURRENT_CACHES.includes(key));
        await Promise.all(oldListoraCaches.map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('message', event => {
    if (event.data?.type === 'AUTHENTICATED') {
        event.waitUntil(caches.delete(PUBLIC_CACHE).then(() => event.ports[0]?.postMessage({ cleared: true })));
    }
});

const isNetworkOnly = url =>
    relativePath(url).startsWith('/auth/') ||
    relativePath(url).startsWith('/logout') ||
    relativePath(url).startsWith('/api/') ||
    relativePath(url).startsWith('/payments') ||
    relativePath(url).startsWith('/chat');

const isStaticAsset = url =>
    relativePath(url).startsWith('/build/') ||
    relativePath(url).startsWith('/images/icons/') ||
    /\.(?:css|js|woff2)$/.test(relativePath(url));

const isPublicImage = url =>
    relativePath(url).startsWith('/images/properties/') && /-(?:thumb)\.(?:avif|webp|jpe?g)$/.test(relativePath(url));

const relativePath = url => BASE_PATH && url.pathname.startsWith(`${BASE_PATH}/`)
    ? url.pathname.slice(BASE_PATH.length)
    : url.pathname;

const isPublicNavigation = url => {
    const path = relativePath(url).replace(/\/$/, '') || '/';

    return path === '/' ||
        path === '/index.html' ||
        path === '/saved' ||
        path === '/join' ||
        path === '/forgot-password' ||
        path === '/offline' ||
        path.startsWith('/properties');
};

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

async function networkFirstPage(request) {
    const cache = await caches.open(PUBLIC_CACHE);

    try {
        const response = await fetch(request, { cache: 'no-store' });
        if (response.ok && response.headers.get('X-Listora-Private') !== '1') {
            await cache.put(request, response.clone());
        } else if (response.headers.get('X-Listora-Private') === '1') {
            await cache.delete(request);
        }

        return response;
    } catch {
        return (await cache.match(request)) || (await caches.match(OFFLINE_URL)) || Response.error();
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
    if (request.mode === 'navigate' && isPublicNavigation(url)) {
        event.respondWith(networkFirstPage(request));
    }
});

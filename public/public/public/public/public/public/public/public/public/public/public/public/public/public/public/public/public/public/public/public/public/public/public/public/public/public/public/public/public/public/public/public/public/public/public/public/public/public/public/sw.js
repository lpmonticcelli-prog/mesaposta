const CACHE_NAME = "mesaposta-log-v2";
const assetsToCache = [
    "/",
    "/dashboard",
    "https://unpkg.com/html5-qrcode" 
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(assetsToCache);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener("activate", (event) => {
    // Apaga os caches velhos para não lotar o celular
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    // Estratégia Network-First
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
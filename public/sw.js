const IMAGE_CACHE_NAME = "classer-image-cache-v1";

const isCacheableImageRequest = (request) => {
    if (request.method !== "GET") {
        return false;
    }

    if (request.destination !== "image") {
        return false;
    }

    const url = new URL(request.url);

    // Cache same-origin and known static media hosts used by the app.
    const allowedHost =
        url.origin === self.location.origin ||
        url.hostname.endsWith("classermedia.com") ||
        url.hostname.endsWith("amazonaws.com") ||
        url.hostname.endsWith("cloudfront.net");

    if (!allowedHost) {
        return false;
    }

    return url.pathname.includes("/assets/images/") || request.destination === "image";
};

const putInCache = async (cache, request, response) => {
    if (!response) {
        return;
    }

    if (response.ok || response.type === "opaque") {
        await cache.put(request, response);
    }
};

self.addEventListener("install", (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", (event) => {
    const { request } = event;

    if (!isCacheableImageRequest(request)) {
        return;
    }

    event.respondWith(
        (async () => {
            const cache = await caches.open(IMAGE_CACHE_NAME);
            const cachedResponse = await cache.match(request);

            const networkResponsePromise = fetch(request)
                .then(async (networkResponse) => {
                    await putInCache(cache, request, networkResponse.clone());
                    return networkResponse;
                })
                .catch(() => null);

            if (cachedResponse) {
                // Stale-while-revalidate behavior.
                void networkResponsePromise;
                return cachedResponse;
            }

            const networkResponse = await networkResponsePromise;
            if (networkResponse) {
                return networkResponse;
            }

            return new Response(null, {
                status: 504,
                statusText: "Gateway Timeout",
            });
        })()
    );
});

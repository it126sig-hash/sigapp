const CACHE_VERSION = "sigapp-pwa-v2";
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PRECACHE_URLS = [
  "offline.html",
  "favicon.ico",
  "manifest.webmanifest",
  "assets/images/pwa/apple-touch-icon.png",
  "assets/images/pwa/icon-192.png",
  "assets/images/pwa/icon-512.png",
  "assets/images/pwa/maskable-192.png",
  "assets/images/pwa/maskable-512.png",
  "app-assets/images/logo/logo.png",
];

function scopedUrl(path) {
  return new URL(path, self.registration.scope).toString();
}

function isSameOrigin(url) {
  return url.origin === self.location.origin;
}

function shouldBypass(url) {
  const path = url.pathname.toLowerCase();
  return [
    "/api/",
    "/files/",
    "/upload/",
    "/uploads/",
    "/login",
    "/logout",
  ].some((segment) => path.includes(segment));
}

function isStaticAsset(url) {
  const path = url.pathname.toLowerCase();
  return (
    path.includes("/app-assets/") ||
    path.includes("/assets/") ||
    path.endsWith("/favicon.ico") ||
    path.endsWith("/manifest.webmanifest")
  );
}

async function cacheFirst(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);
  if (cached) {
    return cached;
  }

  const response = await fetch(request);
  if (response && response.ok && response.type === "basic") {
    cache.put(request, response.clone());
  }
  return response;
}

async function navigationFallback(request) {
  try {
    return await fetch(request);
  } catch (error) {
    const cache = await caches.open(STATIC_CACHE);
    return cache.match(scopedUrl("offline.html"));
  }
}

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then((cache) => cache.addAll(PRECACHE_URLS.map(scopedUrl)))
      .then(() => self.skipWaiting()),
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((key) => key.startsWith("sigapp-pwa-") && key !== STATIC_CACHE).map((key) => caches.delete(key))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener("fetch", (event) => {
  const { request } = event;

  if (request.method !== "GET") {
    return;
  }

  const url = new URL(request.url);
  if (!isSameOrigin(url) || shouldBypass(url)) {
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(navigationFallback(request));
    return;
  }

  if (isStaticAsset(url)) {
    event.respondWith(cacheFirst(request));
  }
});

self.addEventListener('push', function(event) {
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'SIGAPP';
  const options = {
      body: data.body || 'Ada notifikasi baru',
      icon: scopedUrl('assets/images/pwa/icon-192.png'),
      badge: scopedUrl('assets/images/pwa/icon-192.png'),
      data: { url: data.url || '/' },
      tag: data.tag || 'sigapp-notif',
      renotify: true
  };
  
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const url = event.notification.data.url || '/';
  event.waitUntil(
      clients.matchAll({ type: 'window' }).then(windowClients => {
          for (var i = 0; i < windowClients.length; i++) {
              var client = windowClients[i];
              if (client.url === scopedUrl(url) && 'focus' in client) {
                  return client.focus();
              }
          }
          if (clients.openWindow) {
              return clients.openWindow(url);
          }
      })
  );
});

const CACHE_NAME = 'sayabantu-v6';
const ASSETS_TO_CACHE = [
  '/',
  '/manifest.json',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/icons/icon.svg'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    }).then(() => self.skipWaiting())
  );
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  // Hanya proses GET request
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // PENTING: Jangan interupsi atau cache halaman navigasi dinamis / Livewire
  // agar perpindahan halaman Livewire (wire:navigate) instan & tidak konflik dengan cache lama.
  if (
    event.request.mode === 'navigate' ||
    event.request.headers.get('X-Livewire-Navigate') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/register') ||
    url.pathname.startsWith('/customer') ||
    url.pathname.startsWith('/mitra') ||
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/superadmin') ||
    url.pathname.startsWith('/livewire') ||
    url.pathname.startsWith('/api') ||
    url.pathname.startsWith('/chat') ||
    url.pathname.startsWith('/profile') ||
    url.pathname.startsWith('/settings')
  ) {
    return; // Browser & Livewire menangani langsung via jaringan secara native
  }

  // Untuk aset statis (gambar, icon, css build, js build), gunakan cache-first fallback network
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(event.request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      });
    }).catch(() => {
      return caches.match('/');
    })
  );
});

// =========================================================================
// Web Push Notifications
// =========================================================================
self.addEventListener('push', (event) => {
  let data = {};
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = {
        title: 'SayaBantu',
        body: event.data.text()
      };
    }
  }

  const title = data.title || 'SayaBantu';
  const options = {
    body: data.body || 'Anda memiliki pemberitahuan baru di SayaBantu.',
    icon: data.icon || '/icons/icon-192x192.png',
    badge: data.badge || '/icons/icon-192x192.png',
    tag: data.tag || 'sayabantu-notification',
    renotify: true,
    requireInteraction: Boolean(data.requireInteraction),
    timestamp: Date.now(),
    data: {
      url: data.url || '/',
      type: data.type || 'info',
      isUrgent: Boolean((data.data && data.data.isUrgent) || data.isUrgent),
      time: Date.now()
    },
    actions: [
      { action: 'open', title: 'Buka' },
      { action: 'close', title: 'Tutup' }
    ]
  };

  if (Array.isArray(data.vibrate)) {
    if (data.vibrate.length > 0) {
      options.vibrate = data.vibrate;
    }
  } else {
    options.vibrate = [200, 100, 200];
  }

  if (data.image) {
    options.image = data.image;
  }

  // Selalu tampilkan notifikasi native OS / perangkat (Windows Action Center via Edge/Chrome,
  // heads-up notification tray di Android) baik saat tab aktif, diminimize, maupun ditutup.
  event.waitUntil(
    Promise.all([
      self.registration.showNotification(title, options),
      clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
        windowClients.forEach((client) => {
          if (client.visibilityState === 'visible') {
            client.postMessage({
              type: 'SAYABANTU_PUSH_SYNC',
              payload: {
                title,
                body: options.body,
                url: options.data.url,
                icon: options.icon,
                tag: options.tag,
                type: options.data.type,
                isUrgent: options.data.isUrgent,
              }
            });
          }
        });
      })
    ])
  );
});

// Notification Click Event (Buka URL tujuan saat notifikasi di-klik di HP / desktop)
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'close') {
    return;
  }

  const targetUrl = (event.notification.data && event.notification.data.url)
    ? event.notification.data.url
    : '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (let client of windowClients) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      for (let client of windowClients) {
        if (client.url.startsWith(self.location.origin) && 'focus' in client) {
          return client.focus().then(() => {
            if ('navigate' in client) {
              return client.navigate(targetUrl);
            }
          });
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});


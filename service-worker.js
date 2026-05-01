// Service Worker — AGRE Fitness PWA v2
// Stratégie : Network First pour pages PHP, Cache First pour assets statiques

const CACHE_NAME = 'agre-fitness-v2'

// Seuls les assets garantis sans authentification sont pré-cachés.
// Les pages PHP (dashboard, feed…) sont cachées à la volée lors de la visite.
const PRECACHE_ASSETS = [
  '/index.php',
  '/manifest.json',
  '/assets/images/default-avatar.png',
  '/assets/icons/web-app-manifest-192x192.png',
  '/assets/icons/web-app-manifest-512x512.png',
  '/assets/icons/apple-touch-icon.png',
  '/assets/icons/favicon-96x96.png',
]

// ─── Installation ────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
  )
  self.skipWaiting()
})

// ─── Activation & nettoyage des anciens caches ───────────────────────────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
      )
    )
  )
  self.clients.claim()
})

// ─── Interception des requêtes ───────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
  // Ignorer les requêtes non-GET (POST upload, form submit…)
  if (event.request.method !== 'GET') return

  const url = new URL(event.request.url)

  // Ignorer les CDN externes (Tailwind, Google Fonts, etc.)
  if (url.hostname !== self.location.hostname) return

  // ── Cache First : assets statiques (images, icônes, CSS, JS locaux) ────────
  // On met en cache les images statiques ; on exclut les photos de posts
  // dynamiques (/assets/images/posts/) pour ne pas saturer le cache.
  if (
    url.pathname.startsWith('/assets/') &&
    !url.pathname.startsWith('/assets/images/posts/')
  ) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) return cached
        return fetch(event.request).then((response) => {
          if (response.ok) {
            caches
              .open(CACHE_NAME)
              .then((c) => c.put(event.request, response.clone()))
          }
          return response
        })
      })
    )
    return
  }

  // ── Network First : toutes les pages PHP ─────────────────────────────────
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Mettre en cache uniquement les réponses 200 (pas les redirections auth)
        if (response.ok) {
          caches
            .open(CACHE_NAME)
            .then((c) => c.put(event.request, response.clone()))
        }
        return response
      })
      .catch(() =>
        // Offline : tenter le cache, sinon renvoyer la page de connexion
        caches.match(event.request).then((cached) => {
          if (cached) return cached
          if (event.request.mode === 'navigate') {
            return caches.match('/index.php')
          }
        })
      )
  )
})

// ─── Notifications Push ───────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
  if (!event.data) return
  const data = event.data.json()
  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/assets/images/icon-192x192.png',
      badge: '/assets/images/icon-192x192.png',
      tag: data.tag || 'agre-fitness',
      data: data.url,
    })
  )
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  event.waitUntil(clients.openWindow(event.notification.data || '/'))
})

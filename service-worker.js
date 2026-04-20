// Service Worker pour AGRE Fitness PWA
const CACHE_NAME = 'agre-fitness-v1'
const STATIC_ASSETS = [
  '/',
  'index.php',
  'signup.php',
  'pages/dashboard.php',
  'pages/add_workout.php',
  'pages/feed.php',
  'pages/exercices.php',
  'pages/profile.php',
  'includes/navbar.php',
  'config/db.php',
  'manifest.json',
]

// Installation du Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Cache ouvert')
      return cache.addAll(STATIC_ASSETS)
    }),
  )
  self.skipWaiting()
})

// Activation et nettoyage des anciens caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name)),
      )
    }),
  )
  self.clients.claim()
})

// Stratégie de cache : Network First, fallback sur Cache
self.addEventListener('fetch', (event) => {
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') return

  // Ignorer les requêtes vers l'API ou les dossiers d'uploads
  if (
    event.request.url.includes('api/') ||
    event.request.url.includes('assets/images/')
  ) {
    return
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Mettre en cache les nouvelles ressources réussies
        if (response.status === 200) {
          const responseClone = response.clone()
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone)
          })
        }
        return response
      })
      .catch(() => {
        // Fallback sur le cache en cas d'erreur réseau (Offline)
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse
          }
          // Redirection vers l'accueil si navigation hors ligne
          if (event.request.mode === 'navigate') {
            return caches.match('index.php')
          }
        })
      }),
  )
})

// Gestion des notifications push
self.addEventListener('push', (event) => {
  if (event.data) {
    const data = event.data.json()
    event.waitUntil(
      self.registration.showNotification(data.title, {
        body: data.body,
        icon: 'assets/icons/icon-192x192.png',
        badge: 'assets/icons/icon-72x72.png',
        tag: data.tag || 'agre-fitness',
        data: data.url,
      }),
    )
  }
})

// Clic sur notification
self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  event.waitUntil(clients.openWindow(event.notification.data || '/'))
})

const CACHE_NAME = 'oxford-tutle-system-cache'

const urlsToCache = [
  '/pwa/',
  '/build/site.css',
  '/build/site.js',
  '/build/system.css',
  '/build/system.js'
]

const onInstall = (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache)))
}

const onFetch = (event) => {
  event.respondWith(caches.match(event.request).then(response => response || fetch(event.request)))
}

self.addEventListener('install', onInstall)

self.addEventListener('fetch', onFetch)

// service worker: permite instalar TAMPU en el celular
// guarda los archivos fijos y muestra una pagina si no hay internet
const VERSION = 'tampu-v4';
const FIJOS = ['/offline.html', '/css/estilos.css', '/js/script.js', '/img/logo-160.png', '/img/icono-192.png'];

self.addEventListener('install', e => {
    e.waitUntil(caches.open(VERSION).then(c => c.addAll(FIJOS)));
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(caches.keys().then(claves => Promise.all(claves.filter(k => k !== VERSION).map(k => caches.delete(k)))));
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    const pedido = e.request;
    if (pedido.method !== 'GET' || new URL(pedido.url).origin !== location.origin) {
        return;
    }

    // paginas: siempre del servidor, y si no hay internet la pagina sin conexion
    if (pedido.mode === 'navigate') {
        e.respondWith(fetch(pedido).catch(() => caches.match('/offline.html')));
        return;
    }

    // css y js: del servidor para tener siempre la ultima version, y si falla lo guardado
    if (/\.(css|js)$/.test(new URL(pedido.url).pathname)) {
        e.respondWith(fetch(pedido).then(resp => {
            const copia = resp.clone();
            caches.open(VERSION).then(c => c.put(pedido, copia));
            return resp;
        }).catch(() => caches.match(pedido)));
        return;
    }

    // imagenes: primero lo guardado
    if (/\.(png|jpg|jpeg|webp|svg)$/.test(new URL(pedido.url).pathname)) {
        e.respondWith(caches.match(pedido).then(guardado => guardado || fetch(pedido).then(resp => {
            const copia = resp.clone();
            caches.open(VERSION).then(c => c.put(pedido, copia));
            return resp;
        })));
    }
});

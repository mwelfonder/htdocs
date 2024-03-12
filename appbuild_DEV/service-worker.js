const CACHE_NAME = 'scan4_hbg_app_v3'; // Update the version when assets or logic change
const STATIC_ASSETS = [
    '/index.php',
    '/css/style.css',
    '/images/noimageplaceholder.jpg',
    '/images/canvas_arrow.svg',
    '/images/canvas_he.svg',
    '/images/canvas_hup.svg',
    '/images/canvas_tant.svg',
    '/images/scan4_144x144.jpg',
    '/images/scan4_192x192.jpg',
    '/images/scan4_512x512.jpg',
    '/images/logo_carrier_dgf.png',
    '/images/logo_carrier_gfp.png',
    '/images/logo_carrier_gvg.png',
    '/images/logo_carrier_ugg.png',
    '/images/cloud_loading.gif',
    '/images/cloud_check.png',
    '/images/cloud_error.png',
    '/images/cloud_sync.png',
    '/images/logo_scan4scrm_white.png',
    '/images/background_scan4_login.jpg',
    '/images/loadingdots.gif',
    '/js/main.js',
    '/survey.php',
    '/favicon.ico',
    '/apple-touch-icon.png',
    '/favicon-32x32.png',
    '/favicon-16x16.png',
    '/site.webmanifest',
    '/safari-pinned-tab.svg'
];

const CDN_ASSETS = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/jquery-confirm@3.3/dist/jquery-confirm.min.css',
    'https://cdn.jsdelivr.net/npm/jquery-confirm@3.3/dist/jquery-confirm.min.js',
    'https://unpkg.com/@popperjs/core@2',
    'https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js',
    'https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js',
    'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.min.css',
    'https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js',
    'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js',
    'https://cdn.jsdelivr.net/npm/pdfobject@2.2.12/pdfobject.min.js',
    'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js',
    'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js',
    'https://cdn.jsdelivr.net/npm/tesseract.js@2',
    'https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.es.min.js',
    'https://cdn.jsdelivr.net/npm/hn-html2canvas@4.0.0/src/core.min.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                const fetchPromises = [...STATIC_ASSETS, ...CDN_ASSETS].map(url =>
                    fetch(url, { mode: url.startsWith('http') ? 'no-cors' : 'cors' })
                        .then(response => {
                            if (!response.ok && response.type !== 'opaque') {
                                throw new Error(`Failed to fetch ${url} with status: ${response.status}`);
                            }
                            return cache.put(url, response);
                        })
                        .catch(error => {
                            console.error(`Error fetching ${url}:`, error);
                            throw error;
                        })
                );
                return Promise.all(fetchPromises).catch(error => {
                    console.error('Some assets failed to be fetched and cached during install:', error);
                });
            })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(cache => cache !== CACHE_NAME) // Only delete caches that aren't the current version
                    .map(cache => caches.delete(cache))
            );
        })
    );
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    if (url.protocol === 'chrome-extension:' || event.request.method !== 'GET') {
        return; // Ignore chrome extension requests and non-GET requests
    }

    // Add a check for PDF assets and handle them with a separate strategy
    if (url.pathname.endsWith('.pdf')) {
        event.respondWith(networkFirstStrategy(event.request, 'pdf-cache'));
        return;
    }

    if (STATIC_ASSETS.includes(url.pathname) || CDN_ASSETS.includes(event.request.url)) {
        // Use cache-first for static assets and CDN assets
        event.respondWith(cacheFirstStrategy(event.request));
    } else {
        // Use network-first for other requests like API calls
        event.respondWith(networkFirstStrategy(event.request));
    }
});

self.addEventListener('push', event => {
    const data = event.data.json(); // assuming your push data is JSON

    const title = data.title || 'New Notification';
    const options = {
        body: data.body || 'You have a new notification',
        icon: 'images/notification_icon.png', // path to a notification icon
        badge: 'images/notification_badge.png', // path to a badge icon
        // other options like actions, data, etc.
    };

    event.waitUntil(self.registration.showNotification(title, options));
});


function cacheFirstStrategy(request) {
    return caches.match(request)
        .then(cachedResponse => {
            return cachedResponse || fetch(request)
                .then(response => {
                    const clonedResponse = response.clone();
                    caches.open(CACHE_NAME)
                        .then(cache => cache.put(request, clonedResponse));
                    return response;
                });
        });
}

function networkFirstStrategy(request, cacheName = CACHE_NAME) {
    return fetch(request)
        .then(response => {
            const clonedResponse = response.clone();
            caches.open(cacheName)
                .then(cache => cache.put(request, clonedResponse));
            return response;
        })
        .catch(() => caches.match(request).then(response => response || fetch(request)));
}

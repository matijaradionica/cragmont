const DB_NAME = 'cragmont-offline';
const DB_VERSION = 1;
const ROUTE_STORE = 'routes';

function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains(ROUTE_STORE)) {
                db.createObjectStore(ROUTE_STORE);
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function withStore(mode, fn) {
    const db = await openDb();
    try {
        const tx = db.transaction(ROUTE_STORE, mode);
        const store = tx.objectStore(ROUTE_STORE);
        const result = await fn(store);
        await new Promise((resolve, reject) => {
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
            tx.onabort = () => reject(tx.error);
        });
        return result;
    } finally {
        db.close();
    }
}

async function idbGet(key) {
    return withStore('readonly', (store) => {
        return new Promise((resolve, reject) => {
            const req = store.get(key);
            req.onsuccess = () => resolve(req.result ?? null);
            req.onerror = () => reject(req.error);
        });
    });
}

async function idbSet(key, value) {
    return withStore('readwrite', (store) => {
        return new Promise((resolve, reject) => {
            const req = store.put(value, key);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    });
}

async function fetchAsBlob(url) {
    const response = await fetch(url, { credentials: 'same-origin' });
    if (!response.ok) {
        throw new Error(`Failed to download topo image: ${response.status}`);
    }
    return response.blob();
}

function routeKey(routeId) {
    return `route:${routeId}`;
}

async function saveRouteOffline({ routeId, topoUrl, topoData }) {
    const topoImageBlob = topoUrl ? await fetchAsBlob(topoUrl) : null;
    const payload = {
        routeId,
        savedAt: Date.now(),
        topoUrl: topoUrl ?? null,
        topoData: topoData ?? null,
        topoImageBlob,
    };
    await idbSet(routeKey(routeId), payload);
    return payload;
}

async function loadRouteOffline(routeId) {
    return idbGet(routeKey(routeId));
}

async function getNearbyRoutes({ lat, lng, radius = 10, limit = 20 }) {
    const params = new URLSearchParams({ lat, lng, radius, limit });
    const response = await fetch(`/routes/nearby?${params}`, { credentials: 'same-origin' });
    if (!response.ok) {
        throw new Error(`Failed to fetch nearby routes: ${response.status}`);
    }
    return response.json();
}

async function saveMultipleRoutesOffline(routes, onProgress = null) {
    const results = { succeeded: [], failed: [] };

    for (let i = 0; i < routes.length; i++) {
        const route = routes[i];
        try {
            await saveRouteOffline({
                routeId: String(route.id),
                topoUrl: route.topo_url,
                topoData: route.topo_data,
            });
            results.succeeded.push(route);
            if (onProgress) {
                onProgress({ current: i + 1, total: routes.length, route, success: true });
            }
        } catch (err) {
            results.failed.push({ route, error: err });
            if (onProgress) {
                onProgress({ current: i + 1, total: routes.length, route, success: false, error: err });
            }
        }
    }

    return results;
}

function getUserLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation is not supported by your browser'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                });
            },
            (error) => {
                let message = 'Unable to retrieve your location';
                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Location permission denied';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    message = 'Location information unavailable';
                } else if (error.code === error.TIMEOUT) {
                    message = 'Location request timed out';
                }
                reject(new Error(message));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000, // Cache position for 5 minutes
            }
        );
    });
}

function applyOfflineTopoToViewer({ routeId, topoData, topoImageBlob }) {
    const root = document.querySelector('[data-topo-viewer]');
    if (!root) return false;

    if (!topoImageBlob) return false;

    const blobUrl = URL.createObjectURL(topoImageBlob);
    root.setAttribute('data-topo-url', blobUrl);

    const topoDataEl = root.querySelector('[data-topo-data]');
    if (topoDataEl) {
        topoDataEl.textContent = topoData ? JSON.stringify(topoData) : 'null';
    }

    delete root.dataset.topoInitialized;

    window.__cragmontInitTopo?.();
    window.addEventListener('beforeunload', () => URL.revokeObjectURL(blobUrl), { once: true });
    return true;
}

function applyOfflineTopoToEditor({ topoData, topoImageBlob }) {
    const root = document.querySelector('[data-topo-editor]');
    if (!root) return false;
    if (!topoImageBlob) return false;

    const blobUrl = URL.createObjectURL(topoImageBlob);
    root.setAttribute('data-topo-url', blobUrl);

    const topoDataField = root.querySelector('[data-topo-data]');
    if (topoDataField) {
        topoDataField.value = topoData ? JSON.stringify(topoData) : 'null';
    }

    delete root.dataset.topoInitialized;

    window.__cragmontInitTopo?.();
    window.addEventListener('beforeunload', () => URL.revokeObjectURL(blobUrl), { once: true });
    return true;
}

async function tryOfflineHydrate() {
    const routeId = document.body?.dataset?.routeId ?? document.querySelector('[data-route-id]')?.dataset?.routeId;
    if (!routeId) return;
    if (navigator.onLine) return;

    const saved = await loadRouteOffline(routeId);
    if (!saved) return;

    applyOfflineTopoToViewer(saved);
    applyOfflineTopoToEditor(saved);
}

function initOffline() {
    window.addEventListener('cragmont:offline-route-data', async (e) => {
        const detail = e.detail ?? {};
        const routeId = String(detail.routeId ?? '');
        if (!routeId) return;

        try {
            const saved = await saveRouteOffline({
                routeId,
                topoUrl: detail.topoUrl ?? null,
                topoData: detail.topoData ?? null,
            });
            window.dispatchEvent(new CustomEvent('cragmont:offline-saved', { detail: { routeId: saved.routeId } }));
        } catch (err) {
            window.dispatchEvent(
                new CustomEvent('cragmont:offline-save-failed', {
                    detail: { routeId, message: err instanceof Error ? err.message : String(err) },
                }),
            );
        }
    });

    window.addEventListener('cragmont:precache-nearby', async (e) => {
        const detail = e.detail ?? {};
        const radius = detail.radius ?? 10;
        const limit = detail.limit ?? 20;

        try {
            const position = await getUserLocation();
            window.dispatchEvent(new CustomEvent('cragmont:precache-location-acquired', { detail: position }));

            const { routes, count } = await getNearbyRoutes({
                lat: position.lat,
                lng: position.lng,
                radius,
                limit,
            });

            if (routes.length === 0) {
                window.dispatchEvent(
                    new CustomEvent('cragmont:precache-complete', {
                        detail: { succeeded: [], failed: [], message: 'No nearby routes found with topo diagrams' },
                    })
                );
                return;
            }

            const results = await saveMultipleRoutesOffline(routes, (progress) => {
                window.dispatchEvent(new CustomEvent('cragmont:precache-progress', { detail: progress }));
            });

            window.dispatchEvent(new CustomEvent('cragmont:precache-complete', { detail: results }));
        } catch (err) {
            window.dispatchEvent(
                new CustomEvent('cragmont:precache-failed', {
                    detail: { message: err instanceof Error ? err.message : String(err) },
                })
            );
        }
    });

    tryOfflineHydrate().catch(() => {});
    window.addEventListener('online', () => window.dispatchEvent(new CustomEvent('cragmont:online')));
    window.addEventListener('offline', () => window.dispatchEvent(new CustomEvent('cragmont:offline')));
}

document.addEventListener('DOMContentLoaded', initOffline);
document.addEventListener('livewire:navigated', initOffline);

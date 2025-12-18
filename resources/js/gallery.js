function initRouteGalleries() {
    const galleries = document.querySelectorAll('[data-route-gallery]');
    galleries.forEach((gallery) => {
        if (gallery.dataset.initialized === 'true') return;
        gallery.dataset.initialized = 'true';

        const items = Array.from(gallery.querySelectorAll('[data-route-gallery-item]'));
        const lightbox = gallery.querySelector('[data-route-gallery-lightbox]');
        const lightboxImg = gallery.querySelector('[data-route-gallery-lightbox-img]');
        const closeButtons = gallery.querySelectorAll('[data-route-gallery-close]');
        const backdrop = gallery.querySelector('[data-route-gallery-backdrop]');
        const prevButton = gallery.querySelector('[data-route-gallery-prev]');
        const nextButton = gallery.querySelector('[data-route-gallery-next]');
        const zoomInButton = gallery.querySelector('[data-route-gallery-zoom-in]');
        const zoomOutButton = gallery.querySelector('[data-route-gallery-zoom-out]');
        const zoomResetButton = gallery.querySelector('[data-route-gallery-zoom-reset]');

        if (!lightbox || !lightboxImg) return;

        let currentIndex = 0;

        // Zoom and pan state
        const MIN_ZOOM = 1;
        const MAX_ZOOM = 4;
        let zoom = 1;
        let panX = 0;
        let panY = 0;
        let isPanning = false;
        let startX = 0;
        let startY = 0;

        // Touch state for pinch-to-zoom
        const activePointers = new Map();
        const pinchState = { active: false, startDistance: 0, startZoom: 1, startPanX: 0, startPanY: 0 };

        const clampZoom = (value) => Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, value));

        const applyTransform = () => {
            lightboxImg.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
            lightboxImg.style.transformOrigin = 'center center';
            lightboxImg.style.transition = 'none';
        };

        const resetTransform = () => {
            zoom = 1;
            panX = 0;
            panY = 0;
            applyTransform();
        };

        const setZoom = (newZoom, centerX = null, centerY = null) => {
            const oldZoom = zoom;
            zoom = clampZoom(newZoom);

            // If zooming to a specific point, adjust pan to keep that point centered
            if (centerX !== null && centerY !== null && oldZoom !== zoom) {
                const rect = lightboxImg.getBoundingClientRect();
                const imgCenterX = rect.left + rect.width / 2;
                const imgCenterY = rect.top + rect.height / 2;

                const offsetX = centerX - imgCenterX;
                const offsetY = centerY - imgCenterY;

                const zoomRatio = zoom / oldZoom;
                panX = (panX - offsetX) * zoomRatio + offsetX;
                panY = (panY - offsetY) * zoomRatio + offsetY;
            }

            applyTransform();
        };

        const open = (index) => {
            currentIndex = index;
            lightboxImg.src = items[currentIndex].dataset.fullsrc;
            lightbox.classList.remove('hidden');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
            resetTransform();
        };

        const close = () => {
            lightbox.classList.add('hidden');
            lightboxImg.src = '';
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
            resetTransform();
        };

        const show = (delta) => {
            if (!items.length) return;
            currentIndex = (currentIndex + delta + items.length) % items.length;
            lightboxImg.src = items[currentIndex].dataset.fullsrc;
            resetTransform();
        };

        // Mouse/touch pan handlers
        const onPointerDown = (e) => {
            if (e.target !== lightboxImg) return;

            activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });

            if (activePointers.size === 2) {
                // Two fingers - start pinch
                e.preventDefault();
                const points = Array.from(activePointers.values());
                pinchState.active = true;
                pinchState.startZoom = zoom;
                pinchState.startPanX = panX;
                pinchState.startPanY = panY;
                pinchState.startDistance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y) || 1;
                isPanning = false;
            } else if (activePointers.size === 1 && zoom > MIN_ZOOM) {
                // One finger/mouse - start pan
                isPanning = true;
                startX = e.clientX - panX;
                startY = e.clientY - panY;
                lightboxImg.style.cursor = 'grabbing';
            }
        };

        const onPointerMove = (e) => {
            if (!activePointers.has(e.pointerId)) return;

            activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });

            if (pinchState.active && activePointers.size === 2) {
                // Pinch-to-zoom
                e.preventDefault();
                const points = Array.from(activePointers.values());
                const distance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y) || 1;
                const scale = distance / pinchState.startDistance;

                const newZoom = clampZoom(pinchState.startZoom * scale);
                const zoomRatio = newZoom / pinchState.startZoom;

                zoom = newZoom;
                panX = pinchState.startPanX * zoomRatio;
                panY = pinchState.startPanY * zoomRatio;

                applyTransform();
            } else if (isPanning && activePointers.size === 1) {
                // Pan
                e.preventDefault();
                panX = e.clientX - startX;
                panY = e.clientY - startY;
                applyTransform();
            }
        };

        const onPointerUp = (e) => {
            activePointers.delete(e.pointerId);
            if (activePointers.size < 2) {
                pinchState.active = false;
            }
            if (activePointers.size === 0) {
                isPanning = false;
                lightboxImg.style.cursor = zoom > MIN_ZOOM ? 'grab' : 'default';
            }
        };

        const onPointerCancel = (e) => {
            onPointerUp(e);
        };

        // Zoom button handlers
        zoomInButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            setZoom(zoom + 0.5);
        });

        zoomOutButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            setZoom(zoom - 0.5);
        });

        zoomResetButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            resetTransform();
        });

        // Mouse wheel zoom
        lightboxImg.addEventListener('wheel', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const delta = e.deltaY;
            const zoomChange = delta > 0 ? -0.1 : 0.1;
            setZoom(zoom + zoomChange, e.clientX, e.clientY);
        }, { passive: false });

        // Touch and mouse events
        lightboxImg.addEventListener('pointerdown', onPointerDown);
        lightboxImg.addEventListener('pointermove', onPointerMove);
        lightboxImg.addEventListener('pointerup', onPointerUp);
        lightboxImg.addEventListener('pointercancel', onPointerCancel);

        // Prevent default touch actions
        lightboxImg.style.touchAction = 'none';
        lightboxImg.style.userSelect = 'none';

        // Double-click/tap to reset
        lightboxImg.addEventListener('dblclick', (e) => {
            e.stopPropagation();
            resetTransform();
        });

        items.forEach((item, index) => {
            item.addEventListener('click', () => open(index));
        });

        closeButtons.forEach((btn) => btn.addEventListener('click', () => close()));
        backdrop?.addEventListener('click', () => close());
        prevButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            show(-1);
        });
        nextButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            show(1);
        });

        document.addEventListener('keydown', (e) => {
            if (lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') show(-1);
            if (e.key === 'ArrowRight') show(1);
        });
    });
}

document.addEventListener('DOMContentLoaded', initRouteGalleries);
document.addEventListener('livewire:navigated', initRouteGalleries);


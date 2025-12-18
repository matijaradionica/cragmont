let fabricPromise = null;
let cropperPromise = null;

async function loadFabric() {
    if (!fabricPromise) {
        fabricPromise = import('https://cdn.jsdelivr.net/npm/fabric@6.9.0/+esm').then((fabric) => {
            if (fabric.Object?.prototype?.toObject && !fabric.Object.prototype.__cragmontTopoPatched) {
                const originalToObject = fabric.Object.prototype.toObject;
                fabric.Object.prototype.toObject = function toObject(propertiesToInclude) {
                    const base = originalToObject.call(this, propertiesToInclude);
                    if (this.dataType) base.dataType = this.dataType;
                    if (this.customData) base.customData = this.customData;
                    return base;
                };
                fabric.Object.prototype.__cragmontTopoPatched = true;
            }
            return fabric;
        });
    }
    return fabricPromise;
}

async function loadCropper() {
    if (cropperPromise) return cropperPromise;

    if (window.Cropper) {
        cropperPromise = Promise.resolve(window.Cropper);
        return cropperPromise;
    }

    cropperPromise = new Promise((resolve, reject) => {
        const existing = document.querySelector('script[data-cropperjs]');
        if (existing && window.Cropper) {
            resolve(window.Cropper);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js';
        script.async = true;
        script.dataset.cropperjs = 'true';
        script.onload = () => (window.Cropper ? resolve(window.Cropper) : reject(new Error('Cropper failed to load')));
        script.onerror = () => reject(new Error('Cropper failed to load'));
        document.head.appendChild(script);
    });

    return cropperPromise;
}

function parseTopoData(rawValue) {
    if (!rawValue) return null;
    const trimmed = String(rawValue).trim();
    if (!trimmed || trimmed === 'null') return null;
    try {
        const parsed = JSON.parse(trimmed);
        if (typeof parsed === 'string') {
            const nested = parsed.trim();
            if (nested.startsWith('{') || nested.startsWith('[')) {
                try {
                    return JSON.parse(nested);
                } catch {
                    return null;
                }
            }
        }
        return parsed;
    } catch {
        return null;
    }
}

function readTopoDataFromElement(element) {
    if (!element) return null;
    if (element.tagName === 'SCRIPT') return element.textContent;
    return element.value;
}

function setBackgroundImageCompat(canvas, img) {
    if (typeof canvas.setBackgroundImage === 'function') {
        canvas.setBackgroundImage(img, () => canvas.renderAll());
        return;
    }
    canvas.backgroundImage = img;
    canvas.requestRenderAll();
}

function lockCanvasScroll(isLocked) {
    document.documentElement.classList.toggle('overflow-hidden', isLocked);
    document.body.classList.toggle('overflow-hidden', isLocked);
}

function getLastPathPoint(pathCommands) {
    for (let i = pathCommands.length - 1; i >= 0; i -= 1) {
        const cmd = pathCommands[i];
        if (!Array.isArray(cmd) || !cmd.length) continue;
        const type = cmd[0];

        if (type === 'Q' && cmd.length >= 5) return { x: cmd[3], y: cmd[4] };
        if (type === 'C' && cmd.length >= 7) return { x: cmd[5], y: cmd[6] };
        if ((type === 'L' || type === 'M') && cmd.length >= 3) return { x: cmd[1], y: cmd[2] };
    }
    return null;
}

function pathPointToCanvasPoint(fabric, path, point) {
    const local = new fabric.Point(point.x - path.pathOffset.x, point.y - path.pathOffset.y);
    return fabric.util.transformPoint(local, path.calcTransformMatrix());
}

function getInfoMarkerTarget(target) {
    if (!target) return null;
    if (target.dataType === 'info-marker') return target;
    if (target.group?.dataType === 'info-marker') return target.group;
    return null;
}

async function initTopoEditors() {
    const editorRoots = document.querySelectorAll('[data-topo-editor]');
    if (!editorRoots.length) return;

    const fabric = await loadFabric();

    editorRoots.forEach((root) => {
        if (root.dataset.topoInitialized === 'true') return;

        const fileInput = root.querySelector('[data-topo-file]');
        const canvasElement = root.querySelector('canvas[data-topo-canvas]');
        const wrap = root.querySelector('[data-topo-wrap]');
        const topoDataField = root.querySelector('[data-topo-data]');
        const undoButton = root.querySelector('[data-topo-undo]');
        const redoButton = root.querySelector('[data-topo-redo]');
        const clearButton = root.querySelector('[data-topo-clear]');
        const toolButtons = root.querySelectorAll('[data-topo-tool]');
        const colorInput = root.querySelector('[data-topo-color]');
        const widthInput = root.querySelector('[data-topo-width]');
        const markerModal = root.querySelector('[data-topo-marker-modal]');
        const markerTitle = root.querySelector('[data-topo-marker-title]');
        const markerDescription = root.querySelector('[data-topo-marker-description]');
        const markerSave = root.querySelector('[data-topo-marker-save]');
        const markerCancelButtons = root.querySelectorAll('[data-topo-marker-cancel]');
        const cropModal = root.querySelector('[data-topo-crop-modal]');
        const cropImage = root.querySelector('[data-topo-crop-image]');
        const cropApply = root.querySelector('[data-topo-crop-apply]');
        const cropCancelButtons = root.querySelectorAll('[data-topo-crop-cancel]');

        if (!fileInput || !canvasElement || !wrap || !topoDataField) return;

        root.dataset.topoInitialized = 'true';

        const MAX_BASE_DIMENSION = 2000;
        const history = [];
        const redoStack = [];
        let isRestoring = false;
        let currentImageUrl = null;
        let currentTool = 'draw';
        let pendingInfoMarker = null;
        let cropper = null;
        let cropObjectUrl = null;
        let pendingCropFile = null;

        const canvas = new fabric.Canvas(canvasElement, {
            selection: true,
            preserveObjectStacking: true,
        });
        canvas.targetFindTolerance = 18;

        function setTool(tool) {
            currentTool = tool;
            const isDraw = tool === 'draw';
            canvas.isDrawingMode = isDraw;
            if (isDraw) {
                canvas.selection = true;
                canvas.defaultCursor = 'default';
                setBrush();
                canvas.getObjects().forEach((obj) => {
                    obj.selectable = true;
                    obj.evented = true;
                });
            } else {
                canvas.selection = false;
                canvas.defaultCursor = 'crosshair';
                canvas.getObjects().forEach((obj) => {
                    if (obj.dataType === 'info-marker') {
                        obj.selectable = false;
                        obj.evented = true;
                        obj.hoverCursor = 'pointer';
                        return;
                    }
                    obj.selectable = false;
                    obj.evented = false;
                });
            }

            toolButtons?.forEach((btn) => {
                const isActive = btn.getAttribute('data-topo-tool') === tool;
                btn.classList.toggle('bg-indigo-600', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('hover:bg-indigo-700', isActive);
                btn.classList.toggle('bg-gray-200', !isActive);
                btn.classList.toggle('text-gray-800', !isActive);
                btn.classList.toggle('hover:bg-gray-300', !isActive);
            });
        }

        function openMarkerModal(marker) {
            if (!markerModal || !markerTitle || !markerDescription || !markerSave) return;
            pendingInfoMarker = marker;
            markerTitle.value = marker?.customData?.title ?? '';
            markerDescription.value = marker?.customData?.description ?? '';
            markerModal.classList.remove('hidden');
            markerTitle.focus();
        }

        function closeMarkerModal() {
            if (!markerModal) return;
            if (pendingInfoMarker?.__cragmontNew) {
                const hasData = Boolean(
                    pendingInfoMarker.customData?.title?.trim() || pendingInfoMarker.customData?.description?.trim()
                );
                if (!hasData) {
                    canvas.remove(pendingInfoMarker);
                    canvas.requestRenderAll();
                    pushHistory();
                    saveTopoData();
                }
            }
            pendingInfoMarker = null;
            markerModal.classList.add('hidden');
        }

        const closeCropModal = () => {
            if (!cropModal) return;
            cropModal.classList.add('hidden');
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (cropObjectUrl) {
                URL.revokeObjectURL(cropObjectUrl);
                cropObjectUrl = null;
            }
            pendingCropFile = null;
        };

        const openCropModal = async (file) => {
            if (!cropModal || !cropImage) return false;
            const Cropper = await loadCropper();

            pendingCropFile = file;
            cropModal.classList.remove('hidden');

            cropObjectUrl = URL.createObjectURL(file);
            cropImage.src = cropObjectUrl;

            await new Promise((resolve) => {
                cropImage.onload = resolve;
            });

            cropper = new Cropper(cropImage, {
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,
            });

            return true;
        };

        const pushHistory = () => {
            if (isRestoring) return;
            const snapshot = canvas.toJSON();
            delete snapshot.backgroundImage;
            delete snapshot.overlayImage;
            history.push(snapshot);
            if (history.length > 100) history.shift();
            redoStack.length = 0;
        };

        function setBrush() {
            canvas.isDrawingMode = true;
            canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
            canvas.freeDrawingBrush.color = colorInput?.value ?? '#ef4444';
            canvas.freeDrawingBrush.width = Number(widthInput?.value ?? 6);
        }

        const setCanvasCssSize = () => {
            const rect = wrap.getBoundingClientRect();
            if (!rect.width || !canvas.width || !canvas.height) return;
            const cssWidth = rect.width;
            const cssHeight = Math.round(cssWidth * (canvas.height / canvas.width));
            canvas.setDimensions({ width: cssWidth, height: cssHeight }, { cssOnly: true });
            canvas.calcOffset();
        };

        const clearDrawing = () => {
            canvas.getObjects().forEach((obj) => canvas.remove(obj));
            canvas.requestRenderAll();
            pushHistory();
        };

        const restoreSnapshot = async (snapshot) => {
            if (!snapshot) return;
            isRestoring = true;
            const backgroundImage = canvas.backgroundImage;
            await canvas.loadFromJSON(snapshot);
            if (backgroundImage) {
                setBackgroundImageCompat(canvas, backgroundImage);
            } else {
                canvas.renderAll();
            }
            isRestoring = false;
        };

        const saveTopoData = () => {
            if (!currentImageUrl || !canvas.width || !canvas.height) {
                topoDataField.value = 'null';
                return;
            }

            const existing = parseTopoData(readTopoDataFromElement(topoDataField));
            const payload = existing && typeof existing === 'object' ? existing : { version: 1 };

            payload.fabric = canvas.toJSON();
            delete payload.fabric.backgroundImage;
            delete payload.fabric.overlayImage;
            payload.base = payload.base ?? { width: canvas.width, height: canvas.height, scale: 1 };

            topoDataField.value = JSON.stringify(payload);
        };

        const loadTopoData = async (topoData) => {
            if (!topoData?.fabric || !topoData?.base) return;
            const { width, height } = topoData.base;
            canvas.setDimensions({ width, height });
            setCanvasCssSize();
            setTool(currentTool);
            await restoreSnapshot(topoData.fabric);
            pushHistory();
        };

        const setBackgroundImageFromUrl = async (imageUrl) => {
            currentImageUrl = imageUrl;
            const img = await fabric.FabricImage.fromURL(imageUrl, { crossOrigin: 'anonymous' });
            const naturalWidth = img.width ?? 0;
            const naturalHeight = img.height ?? 0;
            if (!naturalWidth || !naturalHeight) throw new Error('Invalid image dimensions.');

            const scale = Math.min(1, MAX_BASE_DIMENSION / naturalWidth, MAX_BASE_DIMENSION / naturalHeight);
            const baseWidth = Math.round(naturalWidth * scale);
            const baseHeight = Math.round(naturalHeight * scale);

            canvas.setDimensions({ width: baseWidth, height: baseHeight });
            img.set({
                left: 0,
                top: 0,
                scaleX: scale,
                scaleY: scale,
                selectable: false,
                evented: false,
            });

            setBackgroundImageCompat(canvas, img);
            setCanvasCssSize();
            setTool(currentTool);
            pushHistory();

            const existing = String(topoDataField.value ?? '').trim();
            if (!existing || existing === 'null') {
                const initial = canvas.toJSON();
                delete initial.backgroundImage;
                delete initial.overlayImage;
                topoDataField.value = JSON.stringify({
                    version: 1,
                    image: { width: naturalWidth, height: naturalHeight },
                    base: { width: baseWidth, height: baseHeight, scale },
                    fabric: initial,
                });
            }
        };

        undoButton?.addEventListener('click', async () => {
            if (history.length <= 1) return;
            const current = history.pop();
            if (current) redoStack.push(current);
            await restoreSnapshot(history[history.length - 1]);
        });

        redoButton?.addEventListener('click', async () => {
            const next = redoStack.pop();
            if (!next) return;
            history.push(next);
            await restoreSnapshot(next);
        });

        clearButton?.addEventListener('click', () => clearDrawing());

        toolButtons?.forEach((btn) => {
            btn.addEventListener('click', () => setTool(btn.getAttribute('data-topo-tool') || 'draw'));
        });

        colorInput?.addEventListener('input', (e) => {
            if (!canvas.freeDrawingBrush) return;
            canvas.freeDrawingBrush.color = e.target.value;
        });

        widthInput?.addEventListener('input', (e) => {
            if (!canvas.freeDrawingBrush) return;
            canvas.freeDrawingBrush.width = Number(e.target.value);
        });

        markerSave?.addEventListener('click', () => {
            if (!pendingInfoMarker) return;
            pendingInfoMarker.customData = {
                title: markerTitle?.value?.trim() ?? '',
                description: markerDescription?.value?.trim() ?? '',
            };
            pendingInfoMarker.__cragmontNew = false;
            canvas.requestRenderAll();
            pushHistory();
            saveTopoData();
            closeMarkerModal();
        });

        markerCancelButtons?.forEach((btn) => {
            btn.addEventListener('click', () => closeMarkerModal());
        });

        cropCancelButtons?.forEach((btn) => {
            btn.addEventListener('click', () => {
                fileInput.value = '';
                closeCropModal();
            });
        });

        cropApply?.addEventListener('click', async () => {
            if (!cropper || !pendingCropFile) return;

            const croppedCanvas = cropper.getCroppedCanvas({
                maxWidth: 2000,
                maxHeight: 2000,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!croppedCanvas) return;

            const blob = await new Promise((resolve) => croppedCanvas.toBlob(resolve, 'image/jpeg', 0.9));
            if (!blob) return;

            const croppedFile = new File([blob], 'topo.jpg', { type: blob.type });
            const dt = new DataTransfer();
            dt.items.add(croppedFile);
            fileInput.files = dt.files;

            topoDataField.value = 'null';
            clearDrawing();

            const objectUrl = URL.createObjectURL(croppedFile);
            try {
                await setBackgroundImageFromUrl(objectUrl);
                saveTopoData();
            } finally {
                setTimeout(() => URL.revokeObjectURL(objectUrl), 5000);
            }

            closeCropModal();
        });

        canvas.on('path:created', () => {
            // Intentionally no-op; we re-handle below with markers.
        });
        canvas.off('path:created');
        canvas.on('path:created', (event) => {
            const path = event?.path;
            if (!path?.path?.length) {
                pushHistory();
                saveTopoData();
                return;
            }

            const startCmd = path.path[0];
            const startLocal =
                Array.isArray(startCmd) && startCmd[0] === 'M' && startCmd.length >= 3
                    ? { x: startCmd[1], y: startCmd[2] }
                    : null;
            const endLocal = getLastPathPoint(path.path);
            if (!startLocal || !endLocal) {
                pushHistory();
                saveTopoData();
                return;
            }

            const start = pathPointToCanvasPoint(fabric, path, startLocal);
            const end = pathPointToCanvasPoint(fabric, path, endLocal);
            const markerColor = path.stroke || colorInput?.value || '#ef4444';
            const radius = 7;

            const startCircle = new fabric.Circle({
                left: start.x,
                top: start.y,
                originX: 'center',
                originY: 'center',
                radius,
                fill: markerColor,
                selectable: false,
                evented: false,
            });

            const endCircle = new fabric.Circle({
                left: end.x,
                top: end.y,
                originX: 'center',
                originY: 'center',
                radius,
                fill: markerColor,
                selectable: false,
                evented: false,
            });

            // Group the line + markers so edits/deletes keep them together.
            canvas.remove(path);
            const group = new fabric.Group([path, startCircle, endCircle], {
                selectable: true,
                evented: true,
            });
            canvas.add(group);
            canvas.requestRenderAll();

            pushHistory();
            saveTopoData();
        });

        canvas.on('mouse:down', (event) => {
            if (currentTool !== 'info') return;
            if (!canvas.backgroundImage) return;

            const markerTarget = getInfoMarkerTarget(event?.target);
            if (markerTarget) {
                openMarkerModal(markerTarget);
                return;
            }

            const pointer = canvas.getPointer(event.e);
            const radius = 9;
            const circle = new fabric.Circle({
                left: pointer.x,
                top: pointer.y,
                originX: 'center',
                originY: 'center',
                radius,
                fill: '#facc15',
                stroke: '#92400e',
                strokeWidth: 2,
                selectable: false,
                evented: false,
            });

            const label = new fabric.Text('\u2139', {
                left: pointer.x,
                top: pointer.y + 0.5,
                originX: 'center',
                originY: 'center',
                fontFamily: 'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial',
                fontSize: 14,
                fontWeight: 800,
                fill: '#111827',
                selectable: false,
                evented: false,
            });

            const marker = new fabric.Group([circle, label], {
                left: pointer.x,
                top: pointer.y,
                originX: 'center',
                originY: 'center',
                selectable: false,
                evented: true,
                hoverCursor: 'pointer',
            });
            marker.dataType = 'info-marker';
            marker.customData = { title: '', description: '' };
            marker.__cragmontNew = true;

            canvas.add(marker);
            canvas.requestRenderAll();
            openMarkerModal(marker);
        });

        canvas.on('object:modified', () => {
            pushHistory();
            saveTopoData();
        });
        canvas.on('object:removed', () => {
            pushHistory();
            saveTopoData();
        });

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            try {
                const opened = await openCropModal(file);
                if (opened) return;
            } catch (err) {
                console.warn('Cropper unavailable, falling back to original image.', err);
            }

            topoDataField.value = 'null';
            clearDrawing();

            const objectUrl = URL.createObjectURL(file);
            try {
                await setBackgroundImageFromUrl(objectUrl);
                saveTopoData();
            } finally {
                setTimeout(() => URL.revokeObjectURL(objectUrl), 5000);
            }
        });

            const initialize = async () => {
            const initialTopoUrl = root.getAttribute('data-topo-url') || null;
            const initialTopoData = parseTopoData(readTopoDataFromElement(topoDataField));

            if (initialTopoUrl) {
                await setBackgroundImageFromUrl(initialTopoUrl);
            } else {
                canvas.setDimensions({ width: 800, height: 500 });
                setCanvasCssSize();
                setTool('draw');
                pushHistory();
            }

            if (initialTopoData) {
                await loadTopoData(initialTopoData);
                saveTopoData();
            }

            window.addEventListener('resize', () => setCanvasCssSize());

            const form = root.closest('form');
            form?.addEventListener('submit', () => saveTopoData());
        };

        initialize().catch((err) => console.error('Topo editor init failed', err));
    });
}

async function initTopoViewers() {
    const viewerRoots = document.querySelectorAll('[data-topo-viewer]');
    if (!viewerRoots.length) return;

    const fabric = await loadFabric();

    viewerRoots.forEach((root) => {
        if (root.dataset.topoInitialized === 'true' || root.dataset.topoInitialized === 'initializing') {
            // Already initialized or currently initializing, skip
            return;
        }

        // Mark as initializing to prevent race conditions
        root.dataset.topoInitialized = 'initializing';

        const canvasElement = root.querySelector('canvas[data-topo-canvas]');
        const topoUrl = root.getAttribute('data-topo-url') || null;
        const topoDataField = root.querySelector('[data-topo-data]');
        const topoData = parseTopoData(readTopoDataFromElement(topoDataField));
        const tooltip = root.querySelector('[data-topo-tooltip]');
        const lightboxOpen = root.querySelector('[data-topo-lightbox-open]');
        const lightbox = root.querySelector('[data-topo-lightbox]');
        const lightboxCanvasElement = root.querySelector('canvas[data-topo-lightbox-canvas]');
        const lightboxTooltip = root.querySelector('[data-topo-lightbox-tooltip]');
        const lightboxCloseButtons = root.querySelectorAll('[data-topo-lightbox-close]');

        if (!canvasElement || !topoUrl) return;

        root.dataset.topoInitialized = 'true';

        const MAX_BASE_DIMENSION = 2000;
        const isMobile = () => window.innerWidth < 768;
        const createViewer = (viewerRoot, element, tooltipElement, options = {}) => {
            const viewerCanvas = new fabric.Canvas(element, { selection: false, renderOnAddRemove: true });
            // Larger tolerance on mobile for easier tapping
            viewerCanvas.targetFindTolerance = isMobile() ? 60 : 30;
            viewerCanvas.upperCanvasEl.style.touchAction = 'none';

            const MIN_ZOOM = 0.75;
            const MAX_ZOOM = 6;
            const clampZoom = (zoom) => Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, zoom));
            const enablePanAndZoom = options.enablePanAndZoom !== false;
            const getCanvasScaleFromRect = () => {
                const rect = viewerCanvas.upperCanvasEl.getBoundingClientRect();
                if (!rect.width || !rect.height || !viewerCanvas.width || !viewerCanvas.height) {
                    return { rect, scaleX: 1, scaleY: 1 };
                }
                return {
                    rect,
                    scaleX: viewerCanvas.width / rect.width,
                    scaleY: viewerCanvas.height / rect.height,
                };
            };
            const getCanvasPointFromClient = (clientX, clientY) => {
                const { rect, scaleX, scaleY } = getCanvasScaleFromRect();
                return new fabric.Point((clientX - rect.left) * scaleX, (clientY - rect.top) * scaleY);
            };

            const setCanvasCssSize = () => {
                const rect = viewerRoot.getBoundingClientRect();
                if (!rect.width || !viewerCanvas.width || !viewerCanvas.height) return;

                // Update tolerance for mobile/desktop
                viewerCanvas.targetFindTolerance = isMobile() ? 60 : 30;

                if (rect.height && rect.height > 50) {
                    const scale = Math.min(rect.width / viewerCanvas.width, rect.height / viewerCanvas.height);
                    const cssWidth = Math.max(1, Math.floor(viewerCanvas.width * scale));
                    const cssHeight = Math.max(1, Math.floor(viewerCanvas.height * scale));
                    viewerCanvas.setDimensions({ width: cssWidth, height: cssHeight }, { cssOnly: true });
                    viewerCanvas.calcOffset();
                    return;
                }

                const cssWidth = rect.width;
                const cssHeight = Math.round(cssWidth * (viewerCanvas.height / viewerCanvas.width));
                viewerCanvas.setDimensions({ width: cssWidth, height: cssHeight }, { cssOnly: true });
                viewerCanvas.calcOffset();
            };

            const scaleInfoMarkersForMobile = () => {
                if (!isMobile()) return;
                const targetScale = 2;

                viewerCanvas.getObjects().forEach((obj) => {
                    if (obj.dataType === 'info-marker') {
                        // Check if already scaled to avoid re-scaling
                        if (obj.__mobileScaled) return;

                        obj.set({
                            scaleX: targetScale,
                            scaleY: targetScale,
                        });
                        obj.__mobileScaled = true;
                    }
                });
            };

            const applyReadOnly = () => {
                viewerCanvas.selection = false;
                viewerCanvas.defaultCursor = 'default';
                viewerCanvas.hoverCursor = 'default';
                viewerCanvas.moveCursor = 'default';

                scaleInfoMarkersForMobile();

                viewerCanvas.getObjects().forEach((obj) => {
                    const isInfoMarker = obj.dataType === 'info-marker';

                    obj.selectable = false;
                    obj.evented = isInfoMarker;
                    obj.hasControls = false;
                    obj.hasBorders = false;
                    obj.lockMovementX = true;
                    obj.lockMovementY = true;
                    obj.lockRotation = true;
                    obj.lockScalingFlip = true;
                    obj.lockScalingX = true;
                    obj.lockScalingY = true;
                    obj.hoverCursor = isInfoMarker ? 'pointer' : 'default';
                });
            };

            const escapeHtml = (value) =>
                String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

            const hideTooltip = () => {
                if (!tooltipElement) return;
                tooltipElement.classList.add('hidden');
            };

            const showTooltip = (event, marker) => {
                if (!tooltipElement) return;
                const data = marker?.customData;
                if (!data?.title && !data?.description) return;

                const rect = viewerRoot.getBoundingClientRect();
                const clientX = event?.e?.clientX ?? rect.left;
                const clientY = event?.e?.clientY ?? rect.top;

                const title = data?.title ? `<div class="font-semibold mb-0.5">${escapeHtml(data.title)}</div>` : '';
                const desc = data?.description ? `<div class="whitespace-pre-wrap">${escapeHtml(data.description)}</div>` : '';
                tooltipElement.innerHTML = `${title}${desc}`;

                // Position tooltip with smart overflow prevention
                let left = Math.round(clientX - rect.left + 10);
                let top = Math.round(clientY - rect.top + 10);

                // Temporarily show to measure dimensions
                tooltipElement.style.left = `${left}px`;
                tooltipElement.style.top = `${top}px`;
                tooltipElement.classList.remove('hidden');

                const tooltipRect = tooltipElement.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                // Adjust horizontal position if overflowing right edge
                if (tooltipRect.right > viewportWidth - 10) {
                    left = Math.round(clientX - rect.left - tooltipRect.width - 10);
                    if (left < 10) {
                        left = 10;
                    }
                }

                // Adjust vertical position if overflowing bottom edge
                if (tooltipRect.bottom > viewportHeight - 10) {
                    top = Math.round(clientY - rect.top - tooltipRect.height - 10);
                    if (top < 10) {
                        top = 10;
                    }
                }

                tooltipElement.style.left = `${left}px`;
                tooltipElement.style.top = `${top}px`;
            };

            const showTooltipForMarker = (marker) => {
                if (!tooltipElement) return;
                const data = marker?.customData;
                if (!data?.title && !data?.description) return;

                const center = marker.getCenterPoint();
                const viewportPoint = fabric.util.transformPoint(center, viewerCanvas.viewportTransform);
                const canvasRect = viewerCanvas.upperCanvasEl.getBoundingClientRect();
                const rootRect = viewerRoot.getBoundingClientRect();

                const x = rootRect.left + (viewportPoint.x / (viewerCanvas.width || 1)) * canvasRect.width;
                const y = rootRect.top + (viewportPoint.y / (viewerCanvas.height || 1)) * canvasRect.height;

                const title = data?.title ? `<div class="font-semibold mb-0.5">${escapeHtml(data.title)}</div>` : '';
                const desc = data?.description ? `<div class="whitespace-pre-wrap">${escapeHtml(data.description)}</div>` : '';
                tooltipElement.innerHTML = `${title}${desc}`;

                // Position tooltip with smart overflow prevention
                let left = Math.round(x - rootRect.left + 10);
                let top = Math.round(y - rootRect.top + 10);

                // Temporarily show to measure dimensions
                tooltipElement.style.left = `${left}px`;
                tooltipElement.style.top = `${top}px`;
                tooltipElement.classList.remove('hidden');

                const tooltipRect = tooltipElement.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                // Adjust horizontal position if overflowing right edge
                if (tooltipRect.right > viewportWidth - 10) {
                    left = Math.round(x - rootRect.left - tooltipRect.width - 10);
                    // If it would overflow left edge, clamp to viewport
                    if (left < 10) {
                        left = 10;
                    }
                }

                // Adjust vertical position if overflowing bottom edge
                if (tooltipRect.bottom > viewportHeight - 10) {
                    top = Math.round(y - rootRect.top - tooltipRect.height - 10);
                    // If it would overflow top edge, clamp to viewport
                    if (top < 10) {
                        top = 10;
                    }
                }

                tooltipElement.style.left = `${left}px`;
                tooltipElement.style.top = `${top}px`;
            };

            const interaction = {
                isPanning: false,
                lastClientX: 0,
                lastClientY: 0,
                pinnedMarker: null,
            };

            const clearPinnedTooltip = () => {
                interaction.pinnedMarker = null;
                hideTooltip();
            };

            viewerCanvas.on('mouse:down', (ev) => {
                if (pinchState.active || activePointers.size >= 2) return;
                const markerTarget = getInfoMarkerTarget(ev?.target);
                if (markerTarget) {
                    if (interaction.pinnedMarker === markerTarget && tooltipElement && !tooltipElement.classList.contains('hidden')) {
                        clearPinnedTooltip();
                        return;
                    }
                    interaction.pinnedMarker = markerTarget;
                    showTooltipForMarker(markerTarget);
                    return;
                }

                clearPinnedTooltip();
                if (!enablePanAndZoom) return;
                const e = ev?.e;
                if (!e) return;
                interaction.isPanning = true;
                interaction.lastClientX = e.clientX;
                interaction.lastClientY = e.clientY;
                viewerCanvas.defaultCursor = 'grabbing';
            });

            viewerCanvas.on('mouse:move', (ev) => {
                if (pinchState.active || activePointers.size >= 2) return;
                if (interaction.isPanning) {
                    const e = ev?.e;
                    if (!e) return;
                    const dx = e.clientX - interaction.lastClientX;
                    const dy = e.clientY - interaction.lastClientY;
                    interaction.lastClientX = e.clientX;
                    interaction.lastClientY = e.clientY;

                    const { scaleX, scaleY } = getCanvasScaleFromRect();
                    const vpt = viewerCanvas.viewportTransform;
                    vpt[4] += dx * scaleX;
                    vpt[5] += dy * scaleY;
                    viewerCanvas.requestRenderAll();
                    if (interaction.pinnedMarker) showTooltipForMarker(interaction.pinnedMarker);
                    return;
                }

                if (interaction.pinnedMarker) return;
                const markerTarget = getInfoMarkerTarget(ev?.target);
                if (markerTarget) {
                    showTooltip(ev, markerTarget);
                } else {
                    hideTooltip();
                }
            });
            viewerCanvas.on('mouse:up', () => {
                interaction.isPanning = false;
                viewerCanvas.defaultCursor = 'default';
                if (interaction.pinnedMarker) showTooltipForMarker(interaction.pinnedMarker);
            });
            viewerCanvas.on('mouse:out', () => {
                if (interaction.isPanning || interaction.pinnedMarker) return;
                hideTooltip();
            });

            viewerCanvas.on('mouse:wheel', (opt) => {
                if (!enablePanAndZoom) return;
                const e = opt?.e;
                if (!e) return;
                e.preventDefault();
                e.stopPropagation();

                const delta = e.deltaY;
                const zoom = viewerCanvas.getZoom();
                const nextZoom = clampZoom(zoom * Math.pow(0.999, delta));
                const point = getCanvasPointFromClient(e.clientX, e.clientY);
                viewerCanvas.zoomToPoint(point, nextZoom);
                viewerCanvas.calcOffset();
                viewerCanvas.requestRenderAll();
                if (interaction.pinnedMarker) showTooltipForMarker(interaction.pinnedMarker);
            });

            const activePointers = new Map();
            const pinchState = { active: false, startDistance: 0, startZoom: 1 };

            const onPointerDown = (e) => {
                if (!enablePanAndZoom) return;
                activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
                if (activePointers.size === 2) {
                    e.preventDefault();  // Prevent browser zoom when 2 fingers detected
                    const points = Array.from(activePointers.values());
                    pinchState.active = true;
                    pinchState.startZoom = viewerCanvas.getZoom();
                    pinchState.startDistance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y) || 1;
                }
            };
            const onPointerMove = (e) => {
                if (!enablePanAndZoom) return;
                if (!activePointers.has(e.pointerId)) return;
                activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
                if (!pinchState.active || activePointers.size !== 2) return;

                e.preventDefault();
                const points = Array.from(activePointers.values());
                const distance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y) || 1;
                const scale = distance / pinchState.startDistance;
                const nextZoom = clampZoom(pinchState.startZoom * scale);

                const centerX = (points[0].x + points[1].x) / 2;
                const centerY = (points[0].y + points[1].y) / 2;
                const point = getCanvasPointFromClient(centerX, centerY);
                viewerCanvas.zoomToPoint(point, nextZoom);
                viewerCanvas.calcOffset();
                viewerCanvas.requestRenderAll();
                if (interaction.pinnedMarker) showTooltipForMarker(interaction.pinnedMarker);
            };
            const onPointerUp = (e) => {
                activePointers.delete(e.pointerId);
                if (activePointers.size < 2) pinchState.active = false;
            };

            viewerCanvas.upperCanvasEl.addEventListener('pointerdown', onPointerDown, { passive: false });
            viewerCanvas.upperCanvasEl.addEventListener('pointermove', onPointerMove, { passive: false });
            viewerCanvas.upperCanvasEl.addEventListener('pointerup', onPointerUp);
            viewerCanvas.upperCanvasEl.addEventListener('pointercancel', onPointerUp);

            viewerCanvas.upperCanvasEl.addEventListener('dblclick', () => {
                if (!enablePanAndZoom) return;
                viewerCanvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
                viewerCanvas.setZoom(1);
                viewerCanvas.calcOffset();
                viewerCanvas.requestRenderAll();
                if (interaction.pinnedMarker) showTooltipForMarker(interaction.pinnedMarker);
            });

            return { viewerCanvas, setCanvasCssSize, applyReadOnly };
        };

        const smallViewer = createViewer(root, canvasElement, tooltip, { enablePanAndZoom: false });
        let largeViewer = null;

        const load = async () => {
            const img = await fabric.FabricImage.fromURL(topoUrl, { crossOrigin: 'anonymous' });
            const naturalWidth = img.width ?? 0;
            const naturalHeight = img.height ?? 0;
            if (!naturalWidth || !naturalHeight) return;

            let baseWidth = naturalWidth;
            let baseHeight = naturalHeight;
            let scale = 1;

            if (topoData?.base?.width && topoData?.base?.height) {
                baseWidth = topoData.base.width;
                baseHeight = topoData.base.height;
                scale = topoData.base.scale ?? (baseWidth / naturalWidth);
            } else {
                scale = Math.min(1, MAX_BASE_DIMENSION / naturalWidth, MAX_BASE_DIMENSION / naturalHeight);
                baseWidth = Math.round(naturalWidth * scale);
                baseHeight = Math.round(naturalHeight * scale);
            }

            smallViewer.viewerCanvas.setDimensions({ width: baseWidth, height: baseHeight });
            img.set({
                left: 0,
                top: 0,
                scaleX: scale,
                scaleY: scale,
                selectable: false,
                evented: false,
            });

            setBackgroundImageCompat(smallViewer.viewerCanvas, img);
            smallViewer.setCanvasCssSize();

            if (topoData?.fabric?.objects) {
                try {
                    await smallViewer.viewerCanvas.loadFromJSON(topoData.fabric);
                    setBackgroundImageCompat(smallViewer.viewerCanvas, img);
                    smallViewer.applyReadOnly();
                    smallViewer.viewerCanvas.requestRenderAll();

                    // Validate objects loaded correctly
                    const objectCount = smallViewer.viewerCanvas.getObjects().length;
                    if (objectCount === 0 && topoData.fabric.objects.length > 0) {
                        console.warn('Canvas loaded 0 objects but JSON had objects:', topoData.fabric.objects.length);
                    }
                } catch (err) {
                    console.error('Failed to load topo data for small viewer:', err);
                    // Fallback: at least show the image even if objects fail to load
                    smallViewer.applyReadOnly();
                }
            } else {
                smallViewer.applyReadOnly();
            }
            window.addEventListener('resize', () => smallViewer.setCanvasCssSize());
        };

        const openLightbox = async () => {
            if (!lightbox || !lightboxCanvasElement) return;
            lightbox.classList.remove('hidden');
            lockCanvasScroll(true);

            if (!largeViewer) {
                const lightboxContainer = root.querySelector('[data-topo-lightbox-wrap]') ?? lightboxCanvasElement.parentElement ?? lightbox;
                largeViewer = createViewer(lightboxContainer, lightboxCanvasElement, lightboxTooltip, { enablePanAndZoom: true });

                const img = await fabric.FabricImage.fromURL(topoUrl, { crossOrigin: 'anonymous' });
                const naturalWidth = img.width ?? 0;
                const naturalHeight = img.height ?? 0;
                if (!naturalWidth || !naturalHeight) return;

                let baseWidth = naturalWidth;
                let baseHeight = naturalHeight;
                let scale = 1;

                if (topoData?.base?.width && topoData?.base?.height) {
                    baseWidth = topoData.base.width;
                    baseHeight = topoData.base.height;
                    scale = topoData.base.scale ?? (baseWidth / naturalWidth);
                } else {
                    scale = Math.min(1, MAX_BASE_DIMENSION / naturalWidth, MAX_BASE_DIMENSION / naturalHeight);
                    baseWidth = Math.round(naturalWidth * scale);
                    baseHeight = Math.round(naturalHeight * scale);
                }

                largeViewer.viewerCanvas.setDimensions({ width: baseWidth, height: baseHeight });
                img.set({
                    left: 0,
                    top: 0,
                    scaleX: scale,
                    scaleY: scale,
                    selectable: false,
                    evented: false,
                });

                setBackgroundImageCompat(largeViewer.viewerCanvas, img);
                largeViewer.setCanvasCssSize();

                if (topoData?.fabric?.objects) {
                    try {
                        await largeViewer.viewerCanvas.loadFromJSON(topoData.fabric);
                        setBackgroundImageCompat(largeViewer.viewerCanvas, img);
                        largeViewer.applyReadOnly();
                        largeViewer.viewerCanvas.requestRenderAll();

                        // Validate objects loaded correctly
                        const objectCount = largeViewer.viewerCanvas.getObjects().length;
                        if (objectCount === 0 && topoData.fabric.objects.length > 0) {
                            console.warn('Canvas loaded 0 objects but JSON had objects:', topoData.fabric.objects.length);
                        }
                    } catch (err) {
                        console.error('Failed to load topo data for lightbox viewer:', err);
                        // Fallback: at least show the image even if objects fail to load
                        largeViewer.applyReadOnly();
                    }
                } else {
                    largeViewer.applyReadOnly();
                }

                window.addEventListener('resize', () => largeViewer?.setCanvasCssSize());
            } else {
                // Reset viewport transform and zoom when reopening lightbox
                largeViewer.viewerCanvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
                largeViewer.viewerCanvas.setZoom(1);
                largeViewer.setCanvasCssSize();
                largeViewer.viewerCanvas.requestRenderAll();
            }
        };

        const closeLightbox = () => {
            if (!lightbox) return;
            lightbox.classList.add('hidden');
            lockCanvasScroll(false);
        };

        lightboxOpen?.addEventListener('click', () => openLightbox().catch((err) => console.error(err)));
        lightboxCloseButtons?.forEach((btn) => btn.addEventListener('click', () => closeLightbox()));
        lightbox?.addEventListener('click', (e) => {
            if (e.target?.matches?.('[data-topo-lightbox-backdrop]')) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });

        load()
            .then(() => {
                // Mark as fully initialized after successful load
                root.dataset.topoInitialized = 'true';
            })
            .catch((err) => {
                console.error('Topo viewer load failed', err);
                // Reset initialization flag on error to allow retry
                root.dataset.topoInitialized = 'false';
            });
    });
}

function initTopo() {
    initTopoEditors().catch((err) => console.error(err));
    initTopoViewers().catch((err) => console.error(err));
}

// Allow other modules (offline loader) to re-run initialization after mutating DOM.
window.__cragmontInitTopo = initTopo;

document.addEventListener('DOMContentLoaded', initTopo);
document.addEventListener('livewire:navigated', initTopo);

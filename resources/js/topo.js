let fabricPromise = null;

async function loadFabric() {
    if (!fabricPromise) {
        fabricPromise = import('https://cdn.jsdelivr.net/npm/fabric@6.9.0/+esm');
    }
    return fabricPromise;
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
        const colorInput = root.querySelector('[data-topo-color]');
        const widthInput = root.querySelector('[data-topo-width]');

        if (!fileInput || !canvasElement || !wrap || !topoDataField) return;

        root.dataset.topoInitialized = 'true';

        const MAX_BASE_DIMENSION = 2000;
        const history = [];
        const redoStack = [];
        let isRestoring = false;
        let currentImageUrl = null;

        const canvas = new fabric.Canvas(canvasElement, {
            selection: true,
            preserveObjectStacking: true,
        });

        const pushHistory = () => {
            if (isRestoring) return;
            const snapshot = canvas.toJSON();
            delete snapshot.backgroundImage;
            delete snapshot.overlayImage;
            history.push(snapshot);
            if (history.length > 100) history.shift();
            redoStack.length = 0;
        };

        const setBrush = () => {
            canvas.isDrawingMode = true;
            canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
            canvas.freeDrawingBrush.color = colorInput?.value ?? '#ef4444';
            canvas.freeDrawingBrush.width = Number(widthInput?.value ?? 6);
        };

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
            setBrush();
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
            setBrush();
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

        colorInput?.addEventListener('input', (e) => {
            if (!canvas.freeDrawingBrush) return;
            canvas.freeDrawingBrush.color = e.target.value;
        });

        widthInput?.addEventListener('input', (e) => {
            if (!canvas.freeDrawingBrush) return;
            canvas.freeDrawingBrush.width = Number(e.target.value);
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
                setBrush();
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
        if (root.dataset.topoInitialized === 'true') return;

        const canvasElement = root.querySelector('canvas[data-topo-canvas]');
        const topoUrl = root.getAttribute('data-topo-url') || null;
        const topoDataField = root.querySelector('[data-topo-data]');
        const topoData = parseTopoData(readTopoDataFromElement(topoDataField));

        if (!canvasElement || !topoUrl) return;

        root.dataset.topoInitialized = 'true';

        const MAX_BASE_DIMENSION = 2000;
        const canvas = new fabric.StaticCanvas(canvasElement, {
            selection: false,
            renderOnAddRemove: true,
        });

        const setCanvasCssSize = () => {
            const rect = root.getBoundingClientRect();
            if (!rect.width || !canvas.width || !canvas.height) return;
            const cssWidth = rect.width;
            const cssHeight = Math.round(cssWidth * (canvas.height / canvas.width));
            canvas.setDimensions({ width: cssWidth, height: cssHeight }, { cssOnly: true });
            canvas.calcOffset();
        };

        const applyReadOnly = () => {
            canvas.getObjects().forEach((obj) => {
                obj.selectable = false;
                obj.evented = false;
            });
        };

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

            if (topoData?.fabric?.objects) {
                await canvas.loadFromJSON(topoData.fabric);
                setBackgroundImageCompat(canvas, img);
                applyReadOnly();
                canvas.requestRenderAll();
            } else {
                applyReadOnly();
            }

            window.addEventListener('resize', () => setCanvasCssSize());
        };

        load().catch((err) => console.error('Topo viewer load failed', err));
    });
}

function initTopo() {
    initTopoEditors().catch((err) => console.error(err));
    initTopoViewers().catch((err) => console.error(err));
}

document.addEventListener('DOMContentLoaded', initTopo);
document.addEventListener('livewire:navigated', initTopo);

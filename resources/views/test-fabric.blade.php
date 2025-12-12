<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabric.js Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        #canvas {
            border: 2px solid #333;
            margin: 20px 0;
            display: block;
        }
        .log {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #333;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log .error { color: red; }
        .log .success { color: green; }
    </style>
</head>
<body>
    <h1>Fabric.js v6 API Test</h1>
    <button onclick="testFabricAPI()">Test Fabric.js API</button>
    <button onclick="clearLog()">Clear Log</button>

    <canvas id="canvas" width="400" height="300"></canvas>

    <div id="log" class="log"></div>

    <script type="module" defer>
        import * as fabric from 'https://cdn.jsdelivr.net/npm/fabric@6.9.0/+esm';

        window.testFabricAPI = async function() {
            const log = document.getElementById('log');
            const messages = [];

            const logger = (msg, type = 'info') => {
                messages.push(`[${type.toUpperCase()}] ${msg}`);
                log.textContent = messages.join('\n');
                console.log(`[${type}]`, msg);
            };

            try {
                logger('Starting Fabric.js API test...');
                logger('Fabric version: ' + fabric.version || 'unknown');

                // Test 1: Check if FabricImage exists
                logger('Checking FabricImage class...', 'info');
                if (fabric.FabricImage) {
                    logger('✓ FabricImage class found', 'success');
                } else {
                    logger('✗ FabricImage class NOT found', 'error');
                    logger('Available exports: ' + Object.keys(fabric).filter(k => k.includes('Image')).join(', '), 'error');
                    return;
                }

                // Test 2: Check if fromURL method exists
                logger('Checking FabricImage.fromURL method...', 'info');
                if (typeof fabric.FabricImage.fromURL === 'function') {
                    logger('✓ FabricImage.fromURL is a function', 'success');
                } else {
                    logger('✗ FabricImage.fromURL is NOT a function', 'error');
                    return;
                }

                // Test 3: Try to load an image using a simple test image
                logger('Testing image loading with data URL...', 'info');

                // Create a simple test image (1x1 red pixel)
                const testImageDataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

                try {
                    const img = await fabric.FabricImage.fromURL(testImageDataUrl, {
                        crossOrigin: 'anonymous'
                    });

                    logger(`✓ Image loaded successfully (${img.width}x${img.height})`, 'success');
                    logger(`Image properties: width=${img.width}, height=${img.height}, opacity=${img.opacity}`, 'info');

                    // Test 4: Create a canvas and set background
                    logger('Creating canvas and setting background...', 'info');
                    const canvas = new fabric.Canvas('canvas', {
                        width: 400,
                        height: 300
                    });
                    logger('✓ Canvas created', 'success');

                    // Scale and set as background
                    img.scaleToWidth(400);
                    canvas.setBackgroundImage(img, () => {
                        logger('✓ Background image set successfully', 'success');
                        canvas.renderAll();
                    }, {
                        top: 0,
                        left: 0
                    });

                    logger('Test completed successfully!', 'success');

                } catch (err) {
                    logger(`✗ Error loading image: ${err.message}`, 'error');
                    logger(`Stack: ${err.stack}`, 'error');
                }

            } catch (err) {
                logger(`✗ Unexpected error: ${err.message}`, 'error');
                logger(`Stack: ${err.stack}`, 'error');
            }
        };

        window.clearLog = function() {
            document.getElementById('log').textContent = '';
        };
    </script>
</body>
</html>

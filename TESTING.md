# Topo Editor - Playwright E2E Tests

## Overview

This directory contains Playwright E2E tests for the Topo Editor component. Tests verify that the canvas-based drawing functionality works correctly across different browsers.

## Test Files

### 1. `tests/e2e/topo-editor-isolated.spec.js`
**Isolated unit tests** - Self-contained tests that don't require a running web server.

These tests verify core TopoEditor functionality:

- ✓ Editor initializes with brush mode
- ✓ Can switch to polyline mode
- ✓ Can switch back to brush mode
- ✓ Can set line color
- ✓ Can set line width
- ✓ History tracking works
- ✓ Undo functionality works
- ✓ Redo functionality works
- ✓ Clear drawings functionality works
- ✓ Export JSON functionality works
- ✓ Cannot undo when at start
- ✓ Cannot redo when at end
- ✓ canUndo() and canRedo() work correctly

**Run with:**
```bash
npm run test:e2e
```

### 2. `tests/e2e/topo-editor.spec.js`
**Integration tests** - Tests with a full HTML page and Fabric.js integration (requires web server).

Features tested:
- Canvas initialization with brush mode
- Free draw mode with mouse movements
- Polyline mode with click-based drawing
- Undo/redo functionality
- Clear all drawings
- Export canvas to JSON
- Mode switching between brush and polyline
- Multiple drawing actions in history

**Run with:**
```bash
npm run test:e2e -- tests/e2e/topo-editor.spec.js
```

## Setup

### Prerequisites

1. **Install Playwright browsers:**
   ```bash
   npx playwright install
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

### Configuration

Playwright config is in `playwright.config.js`:
- **Test directory:** `tests/e2e/`
- **Browser:** Chromium (can add Firefox, Safari)
- **Reporters:** List format, HTML report
- **Screenshot:** On failure only
- **Video:** Only on failure

## Running Tests

### Run all tests:
```bash
npm run test:e2e
```

### Run specific test file:
```bash
npm run test:e2e -- tests/e2e/topo-editor-isolated.spec.js
```

### Run specific test:
```bash
npm run test:e2e -- --grep "should pass all TopoEditor unit tests"
```

### UI Mode (interactive):
```bash
npm run test:e2e:ui
```

### Debug Mode:
```bash
npm run test:e2e:debug
```

## Test Results

Results are saved to `test-results/` directory:
- `html/` - HTML report (open in browser)
- Traces and videos on failure

View HTML report:
```bash
npx playwright show-report
```

## Test Architecture

### Isolated Tests (`topo-editor-isolated.spec.js`)

The isolated tests use an inline HTML page with:
1. Fabric.js loaded from CDN
2. Simple TopoEditor implementation
3. Test runner utility with assertions
4. No external server required

**Benefits:**
- Fast execution (no server startup)
- Can run offline
- Good for CI/CD pipelines
- Tests core logic independently

**Test Utility Functions:**
- `TestRunner.test(name, fn)` - Register test
- `TestRunner.run()` - Execute all tests
- `TestRunner.assert(condition, message)` - Assert condition
- `TestRunner.assertEqual(actual, expected)` - Assert equality
- `TestRunner.assertGreater(actual, expected)` - Assert greater than
- `TestRunner.assertLess(actual, expected)` - Assert less than

### Integration Tests (`topo-editor.spec.js`)

The integration tests require:
1. Full HTML page with controls
2. File upload simulation
3. Mouse interactions
4. Canvas drawing verification

**Note:** These require a web server running. Adjust the config's `webServer` setting if needed.

## Implementation Details

### TopoEditor Class (In Tests)

Simplified implementation for testing with:
- Canvas initialization via Fabric.js
- Brush mode (free drawing)
- Polyline mode (click-to-draw)
- History tracking (undo/redo)
- JSON export/import
- Line color and width customization

### Key Methods Tested:
```javascript
// Initialization
editor.enableBrushDrawing()
editor.enablePolylineDrawing()

// Drawing
editor.drawPath(points)
editor.setLineColor(color)
editor.setLineWidth(width)

// History
editor.undo()
editor.redo()
editor.canUndo()
editor.canRedo()

// Utility
editor.clearDrawings()
editor.exportJSON()
editor.importJSON(json)
```

## Troubleshooting

### Browsers not found
```bash
npx playwright install
```

### Tests timing out
- Increase timeout in playwright.config.js
- Check if development server is running (for integration tests)

### Flaky tests
- Increase `waitForFunction` timeouts
- Add explicit waits between actions
- Check for race conditions in canvas rendering

## CI/CD Integration

For CI environments, use:
```bash
PLAYWRIGHT_CI=1 npm run test:e2e
```

This will:
- Use headless mode
- Single worker
- Retry failed tests
- Generate detailed reports

## Future Enhancements

1. Add tests for actual image loading
2. Add tests for topo data persistence
3. Add tests for Livewire integration
4. Add performance benchmarks
5. Add visual regression testing
6. Add accessibility testing

## Resources

- [Playwright Documentation](https://playwright.dev/)
- [Fabric.js Documentation](http://fabricjs.com/)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)

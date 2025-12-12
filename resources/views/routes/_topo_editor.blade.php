<div class="space-y-3" data-topo-editor data-topo-url="{{ $route->topo_url ? route('routes.topo', $route) : '' }}">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Topo Image (Optional)
    </label>

    <p class="text-sm text-gray-500">
        Upload an image, then draw the route line on top. Undo/redo is available. Replacing the image clears the drawing.
    </p>

    <input
        type="file"
        name="topo"
        data-topo-file
        accept="image/*"
        class="block w-full text-sm text-gray-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-md file:border-0
            file:text-sm file:font-semibold
            file:bg-indigo-50 file:text-indigo-700
            hover:file:bg-indigo-100"
    >

    @if($route->topo_url)
        <div class="text-sm text-gray-600">
            Current image:
            <a class="text-indigo-600 hover:underline" href="{{ route('routes.topo', $route) }}" target="_blank" rel="noreferrer">
                View
            </a>
        </div>
    @endif

    <textarea name="topo_data" data-topo-data class="hidden">{{ old('topo_data', $route->topo_data ? json_encode($route->topo_data) : 'null') }}</textarea>

    <div class="rounded-lg border border-gray-300 bg-white p-3">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <button type="button" data-topo-undo
                class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs uppercase tracking-widest hover:bg-gray-700">
                Undo
            </button>
            <button type="button" data-topo-redo
                class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs uppercase tracking-widest hover:bg-gray-700">
                Redo
            </button>
            <button type="button" data-topo-clear
                class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-xs uppercase tracking-widest hover:bg-gray-300">
                Clear
            </button>

            <div class="ml-auto flex items-center gap-2">
                <label class="text-xs text-gray-600">Color</label>
                <input type="color" data-topo-color value="#ef4444" class="h-8 w-10 rounded border border-gray-300">
                <label class="text-xs text-gray-600">Width</label>
                <input type="range" data-topo-width min="2" max="16" value="6" class="w-28">
            </div>
        </div>

        <div data-topo-wrap class="w-full">
            <canvas data-topo-canvas></canvas>
        </div>

        <p class="mt-2 text-xs text-gray-500">
            Tip: use a trackpad/mouse for cleaner lines.
        </p>
    </div>

    @error('topo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('topo_data')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

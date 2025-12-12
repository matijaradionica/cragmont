<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Topo Diagram (Optional)
    </label>

    @if($existingTopoUrl && !$removeExisting)
        <!-- Existing Topo Display -->
        <div class="mb-4">
            <div class="relative inline-block">
                <img src="{{ Storage::url($existingTopoUrl) }}" alt="Current topo"
                    class="max-w-xs rounded-lg border border-gray-300">
                <button type="button" wire:click="removeExistingTopo"
                    class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">Current topo diagram. Click X to remove and upload a new one.</p>
        </div>
    @endif

    @if(!$existingTopoUrl || $removeExisting)
        <!-- File Upload Input -->
        <div class="mt-1">
            <input type="file" wire:model="topo" accept="image/*"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100">
        </div>

        <p class="mt-1 text-sm text-gray-500">
            PNG, JPG, JPEG, or WEBP (Max: 5MB)
        </p>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="topo" class="mt-2">
            <span class="text-sm text-indigo-600">Uploading...</span>
        </div>

        <!-- Preview New Upload -->
        @if($topo)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                <div class="relative inline-block">
                    <img src="{{ $topo->temporaryUrl() }}" alt="Topo preview"
                        class="max-w-xs rounded-lg border border-gray-300">
                    <button type="button" wire:click="removeTopo"
                        class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @error('topo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

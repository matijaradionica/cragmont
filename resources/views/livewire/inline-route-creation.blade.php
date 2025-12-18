<div>
    <!-- Trigger Button -->
    <button type="button" wire:click="openModal"
        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create New Route
    </button>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form wire:submit.prevent="createRoute">
                    <!-- Modal Header -->
                    <div class="bg-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white" id="modal-title">
                                Create New Route
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-6">
                            <!-- Route Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Route Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="name" id="name" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location Search -->
                            <div>
                                <label for="locationSearch" class="block text-sm font-medium text-gray-700 mb-1">
                                    Location <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="locationSearch" id="locationSearch"
                                        placeholder="Search for a location..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                                    @if(count($filteredLocations) > 0 && !$location_id)
                                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                        @foreach($filteredLocations as $loc)
                                        <button type="button" wire:click="selectLocation({{ $loc->id }})"
                                            class="w-full text-left px-4 py-2 hover:bg-indigo-50 focus:bg-indigo-100 focus:outline-none">
                                            <div class="font-medium text-gray-900">{{ $loc->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $loc->getFullPath() }}</div>
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @error('location_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if($location_id)
                                    <p class="mt-1 text-sm text-green-600">✓ Location selected</p>
                                @endif
                            </div>

                            <!-- Technical Specifications Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Grade Type -->
                                <div>
                                    <label for="grade_type" class="block text-sm font-medium text-gray-700 mb-1">
                                        Grade System <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="grade_type" id="grade_type" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select grade system...</option>
                                        <option value="UIAA">UIAA</option>
                                        <option value="French">French</option>
                                    </select>
                                    @error('grade_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Grade Value -->
                                <div>
                                    <label for="grade_value" class="block text-sm font-medium text-gray-700 mb-1">
                                        Grade <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="grade_value" id="grade_value" required
                                        placeholder="e.g., 5+, 6a, 7b+"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('grade_value')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Route Type -->
                                <div>
                                    <label for="route_type" class="block text-sm font-medium text-gray-700 mb-1">
                                        Route Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="route_type" id="route_type" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select type...</option>
                                        <option value="Alpine">Alpine</option>
                                        <option value="Sport">Sport</option>
                                        <option value="Traditional">Traditional</option>
                                    </select>
                                    @error('route_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Risk Rating -->
                                <div>
                                    <label for="risk_rating" class="block text-sm font-medium text-gray-700 mb-1">
                                        Risk Rating <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="risk_rating" id="risk_rating" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="None">None</option>
                                        <option value="R">R (Runout)</option>
                                        <option value="X">X (Serious)</option>
                                    </select>
                                    @error('risk_rating')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Length -->
                                <div>
                                    <label for="length_m" class="block text-sm font-medium text-gray-700 mb-1">
                                        Length (meters)
                                    </label>
                                    <input type="number" wire:model="length_m" id="length_m"
                                        min="1" step="1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('length_m')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Pitch Count -->
                                <div>
                                    <label for="pitch_count" class="block text-sm font-medium text-gray-700 mb-1">
                                        Number of Pitches <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="pitch_count" id="pitch_count" required
                                        min="1" max="50"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('pitch_count')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="status" id="status" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="New">New</option>
                                        <option value="Equipped">Equipped</option>
                                        <option value="Needs Repair">Needs Repair</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Approach Description -->
                            <div>
                                <label for="approach_description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Approach Description
                                </label>
                                <textarea wire:model="approach_description" id="approach_description" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Describe how to reach the route..."></textarea>
                                @error('approach_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descent Description -->
                            <div>
                                <label for="descent_description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Descent Description
                                </label>
                                <textarea wire:model="descent_description" id="descent_description" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Describe how to descend from the route..."></textarea>
                                @error('descent_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Required Gear -->
                            <div>
                                <label for="required_gear" class="block text-sm font-medium text-gray-700 mb-1">
                                    Required Gear
                                </label>
                                <textarea wire:model="required_gear" id="required_gear" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="List the gear needed..."></textarea>
                                @error('required_gear')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Topo Editor -->
                            <div wire:ignore>
                                <div class="space-y-3" data-topo-editor data-topo-url="">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Topo Image (Optional)
                                    </label>

                                    <p class="text-sm text-gray-500">
                                        Upload an image, then draw the route line on top. Undo/redo is available. Replacing the image clears the drawing.
                                    </p>

                                    <input
                                        type="file"
                                        name="topo_file"
                                        id="inline_topo_file"
                                        data-topo-file
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100"
                                    >

                                    <textarea name="topo_data" id="inline_topo_data" data-topo-data class="hidden">null</textarea>

                                    <div class="rounded-lg border border-gray-300 bg-white p-3">
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            <button type="button" data-topo-tool="draw"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs uppercase tracking-widest hover:bg-indigo-700">
                                                Draw
                                            </button>
                                            <button type="button" data-topo-tool="info"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-xs uppercase tracking-widest hover:bg-gray-300">
                                                Info Marker
                                            </button>

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

                                    <!-- Info Marker Modal -->
                                    <div data-topo-marker-modal class="fixed inset-0 z-50 hidden">
                                        <div class="absolute inset-0 bg-black/40" data-topo-marker-cancel></div>
                                        <div class="absolute inset-0 flex items-center justify-center p-4">
                                            <div class="w-full max-w-md rounded-lg bg-white shadow-lg border border-gray-200 p-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h3 class="text-sm font-semibold text-gray-900">Info Marker</h3>
                                                    <button type="button" class="text-gray-500 hover:text-gray-700" data-topo-marker-cancel>
                                                        ✕
                                                    </button>
                                                </div>

                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                                        <input type="text" data-topo-marker-title
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="e.g., Crux Move">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                        <textarea rows="4" data-topo-marker-description
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="Add beta, safety notes, gear, etc."></textarea>
                                                    </div>

                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button type="button" data-topo-marker-cancel
                                                            class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-xs uppercase tracking-widest hover:bg-gray-300">
                                                            Cancel
                                                        </button>
                                                        <button type="button" data-topo-marker-save
                                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs uppercase tracking-widest hover:bg-indigo-700">
                                                            Save
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Crop Modal -->
                                    <div data-topo-crop-modal class="fixed inset-0 z-50 hidden">
                                        <div class="absolute inset-0 bg-black/60" data-topo-crop-cancel></div>
                                        <div class="absolute inset-0 z-10 flex items-center justify-center p-4">
                                            <div class="relative w-full max-w-5xl max-h-[90vh] overflow-hidden rounded-lg bg-white shadow-lg border border-gray-200 p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="text-sm font-semibold text-gray-900">Crop Topo Image</div>
                                                    <button type="button" data-topo-crop-cancel
                                                        class="text-gray-500 hover:text-gray-700 px-2 py-1"
                                                        aria-label="Cancel">✕</button>
                                                </div>

                                                <div class="relative w-full max-h-[75vh] overflow-hidden bg-gray-50 rounded border border-gray-200">
                                                    <img data-topo-crop-image alt="Crop topo" class="max-w-full">
                                                </div>

                                                <div class="mt-3 flex items-center justify-end gap-2">
                                                    <button type="button" data-topo-crop-cancel
                                                        class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-xs uppercase tracking-widest hover:bg-gray-300">
                                                        Cancel
                                                    </button>
                                                    <button type="button" data-topo-crop-apply
                                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs uppercase tracking-widest hover:bg-indigo-700">
                                                        Use Cropped Image
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @error('topo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('topo_data')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Route Photos -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Route Photos (Optional, max 10)
                                </label>
                                <input type="file" wire:model="photos" accept="image/*" multiple
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100">
                                @error('photos')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('photos.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div wire:loading wire:target="photos" class="mt-1 text-sm text-indigo-600">
                                    Uploading...
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> Routes created here will be automatically approved and immediately available for selection.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createRoute">Create Route</span>
                            <span wire:loading wire:target="createRoute">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    @endpush

    @push('scripts')
    <script>
        // Re-initialize topo editor when modal is shown
        document.addEventListener('livewire:initialized', function() {
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Check if this is our inline route creation component
                if (el.querySelector('[data-topo-editor]') && window.__cragmontInitTopo) {
                    // Small delay to ensure DOM is ready
                    setTimeout(() => {
                        window.__cragmontInitTopo();

                        // Set up file sync after topo editor is initialized
                        setupTopoFileSync();
                    }, 100);
                }
            });
        });

        function setupTopoFileSync() {
            const topoFileInput = document.getElementById('inline_topo_file');
            const topoDataField = document.getElementById('inline_topo_data');

            if (!topoFileInput || !topoDataField) return;

            // Helper to find the Livewire component
            function findLivewireComponent() {
                const form = topoFileInput.closest('form');
                if (!form) return null;

                const livewireEl = form.closest('[wire\\:id]');
                if (!livewireEl) return null;

                const wireId = livewireEl.getAttribute('wire:id');
                return wireId ? Livewire.find(wireId) : null;
            }

            // Listen for file changes from the topo editor
            topoFileInput.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const component = findLivewireComponent();

                    if (component) {
                        // Upload file to Livewire
                        component.upload('topo', files[0],
                            (uploadedFilename) => {
                                // File uploaded successfully
                                console.log('Topo file uploaded to Livewire');
                            },
                            () => {
                                // Upload progress
                            },
                            (event) => {
                                // Upload error
                                console.error('Topo file upload error:', event);
                            }
                        );
                    }
                }
            });

            // Sync topo_data before form submission
            const form = topoFileInput.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const component = findLivewireComponent();

                    if (component && topoDataField.value && topoDataField.value !== 'null') {
                        // Sync topo_data to Livewire component
                        component.set('topo_data', topoDataField.value);
                    }
                }, true); // Use capture phase to run before Livewire's handler
            }
        }

        // Initial setup when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (window.__cragmontInitTopo) {
                    window.__cragmontInitTopo();
                }
                setupTopoFileSync();
            }, 100);
        });
    </script>
    @endpush
</div>

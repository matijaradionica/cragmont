<div wire:ignore>
    <div id="map-{{ $this->getId() }}" style="height: {{ $height }}; width: 100%; border-radius: 0.5rem; z-index: 0;"></div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initMap_{{ $this->getId() }}();
    });

    // Also trigger on Livewire navigation
    document.addEventListener('livewire:navigated', function() {
        // Small delay to ensure DOM is ready and Leaflet is loaded
        setTimeout(function() {
            initMap_{{ $this->getId() }}();
        }, 50);
    });

    function initMap_{{ $this->getId() }}() {
        // Wait for Leaflet to be available
        if (typeof L === 'undefined') {
            console.warn('Leaflet not loaded yet, retrying...');
            setTimeout(initMap_{{ $this->getId() }}, 100);
            return;
        }

        const mapId = 'map-{{ $this->getId() }}';
        const mapElement = document.getElementById(mapId);

        if (!mapElement) {
            console.warn('Map element not found:', mapId);
            return;
        }

        // Check if element is visible in the DOM
        if (!mapElement.offsetParent && mapElement.style.display !== 'none') {
            console.warn('Map container not visible, retrying...');
            setTimeout(initMap_{{ $this->getId() }}, 100);
            return;
        }

        try {
            // Clean up existing map instance if it exists
            if (mapElement._leaflet_id) {
                // Remove existing map
                if (window['mapInstance_' + mapId]) {
                    try {
                        window['mapInstance_' + mapId].remove();
                    } catch (e) {
                        console.warn('Error removing existing map:', e);
                    }
                    delete window['mapInstance_' + mapId];
                }
                mapElement._leaflet_id = null;
                mapElement.innerHTML = '';
            }

            // Initialize map
            const mapInstance = L.map(mapId, {
                preferCanvas: true,
                worldCopyJump: true,
                maxBounds: [[-90, -180], [90, 180]],
                maxBoundsViscosity: 1.0
            }).setView([{{ $centerLat }}, {{ $centerLng }}], {{ $zoom }});

            // Store map instance globally for cleanup
            window['mapInstance_' + mapId] = mapInstance;

            // Add tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
                noWrap: true,
                bounds: [[-90, -180], [90, 180]]
            }).addTo(mapInstance);

            // Initialize marker cluster group
            const markers = L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            });

            // Add location markers
            const locations = @json($locations);

            locations.forEach(location => {
                if (location.gps_lat && location.gps_lng) {
                    const marker = L.marker([location.gps_lat, location.gps_lng]);

                    // Create popup content
                    const routeCount = location.routes?.length || 0;
                    const popupContent = `
                        <div class="p-2">
                            <h3 class="font-bold text-lg mb-1">${location.name}</h3>
                            ${location.description ? `<p class="text-sm text-gray-600 mb-2">${location.description}</p>` : ''}
                            <p class="text-sm font-medium text-gray-700">
                                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                ${routeCount} approved ${routeCount === 1 ? 'route' : 'routes'}
                            </p>
                            <a href="/locations/${location.id}"
                               class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                View Details
                            </a>
                        </div>
                    `;

                    marker.bindPopup(popupContent);
                    markers.addLayer(marker);
                }
            });

            // Add marker cluster to map
            mapInstance.addLayer(markers);

            // Fix map size issues and fit bounds
            // Use requestAnimationFrame to ensure browser has rendered the map
            requestAnimationFrame(() => {
                setTimeout(() => {
                    try {
                        // Check if map instance and container are still valid
                        if (!mapInstance || !mapElement || !mapElement.offsetParent) {
                            return;
                        }

                        // Check if map container has dimensions
                        const containerSize = mapElement.getBoundingClientRect();
                        if (containerSize.width === 0 || containerSize.height === 0) {
                            return;
                        }

                        // Check if map is properly initialized with a size
                        const mapSize = mapInstance.getSize();
                        if (!mapSize || mapSize.x === 0 || mapSize.y === 0) {
                            return;
                        }

                        mapInstance.invalidateSize();

                        // Fit bounds if multiple locations
                        @if(!$showSingleLocation && count($locations) > 1)
                            if (markers.getLayers().length > 0) {
                                try {
                                    const bounds = markers.getBounds();
                                    if (bounds && bounds.isValid()) {
                                        // Additional check: ensure map panes are initialized
                                        const panes = mapInstance.getPanes();
                                        if (panes && panes.mapPane) {
                                            mapInstance.fitBounds(bounds, {
                                                padding: [50, 50],
                                                animate: false // Disable animation to avoid timing issues
                                            });
                                        }
                                    }
                                } catch (boundsError) {
                                    // Silently fail - map will still work, just won't auto-fit
                                }
                            }
                        @endif
                    } catch (error) {
                        // Silently fail - map will still work
                    }
                }, 300);
            });
        } catch (error) {
            console.error('Error initializing map:', error);
            return;
        }
    }

    // Cleanup on page leave
    document.addEventListener('livewire:navigating', function() {
        const mapId = 'map-{{ $this->getId() }}';
        if (window['mapInstance_' + mapId]) {
            try {
                window['mapInstance_' + mapId].off();
                window['mapInstance_' + mapId].remove();
                delete window['mapInstance_' + mapId];
            } catch (e) {
                console.warn('Error cleaning up map:', e);
            }
        }
    });
</script>
@endpush

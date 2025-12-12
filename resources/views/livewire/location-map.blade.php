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
        const mapId = 'map-{{ $this->getId() }}';
        const mapElement = document.getElementById(mapId);

        if (!mapElement) return;

        // Initialize map
        const map = L.map(mapId).setView([{{ $centerLat }}, {{ $centerLng }}], {{ $zoom }});

        // Add tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

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
        map.addLayer(markers);

        // Fit bounds if multiple locations
        @if(!$showSingleLocation && count($locations) > 1)
            if (markers.getLayers().length > 0) {
                map.fitBounds(markers.getBounds(), { padding: [50, 50] });
            }
        @endif
    });
</script>
@endpush

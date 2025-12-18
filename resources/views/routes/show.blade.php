<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $route->name }}
            </h2>
            <div class="flex space-x-2">
                @auth
                    <a href="{{ route('ascents.create', ['route_id' => $route->id]) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Log Ascent
                    </a>
                @endauth
                @can('update', $route)
                    <a href="{{ route('routes.edit', $route) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Edit
                    </a>
                @endcan
                @can('delete', $route)
                    <form action="{{ route('routes.destroy', $route) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this route?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12" data-route-id="{{ $route->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Technical Specifications -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Technical Specifications</h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Grade</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->grade_type }}: {{ $route->grade_value }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Risk Rating</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->risk_rating }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Route Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->route_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($route->status === 'New') bg-blue-100 text-blue-800
                                        @elseif($route->status === 'Equipped') bg-green-100 text-green-800
                                        @elseif($route->status === 'Needs Repair') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $route->status }}
                                    </span>
                                </dd>
                            </div>
                            @if($route->length_m)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Length</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $route->length_m }}m</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pitches</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->pitch_count }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Approach -->
                    @if($route->approach_description)
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Approach</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $route->approach_description }}</p>
                        </div>
                    @endif

                    <!-- Descent -->
                    @if($route->descent_description)
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Descent</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $route->descent_description }}</p>
                        </div>
                    @endif

                    <!-- Required Gear -->
                    @if($route->required_gear)
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Required Gear</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $route->required_gear }}</p>
                        </div>
                    @endif

                    <livewire:condition-reports.list :routeId="$route->id" />
                    <livewire:condition-reports.create :routeId="$route->id" />

                    @php
                        $galleryPhotos = $route->photos->where('is_topo', false)->sortBy('order')->values();
                    @endphp
                    <div class="bg-white shadow rounded-lg p-6" data-route-gallery>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Gallery</h3>
                            @can('update', $route)
                                <a href="{{ route('routes.edit', $route) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Add photos
                                </a>
                            @endcan
                        </div>

                        @if($galleryPhotos->isEmpty())
                            <p class="text-sm text-gray-500">No photos uploaded yet.</p>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($galleryPhotos as $photo)
                                    <button type="button"
                                        data-route-gallery-item
                                        data-fullsrc="{{ route('routes.photos.show', [$route, $photo]) }}"
                                        class="relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50 hover:ring-2 hover:ring-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <img src="{{ route('routes.photos.show', [$route, $photo]) }}"
                                            alt="Route photo"
                                            class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>

                            <div data-route-gallery-lightbox class="fixed inset-0 z-50 hidden">
                                <div data-route-gallery-backdrop class="absolute inset-0 bg-black/70"></div>
                                <div class="absolute inset-0 z-10 flex items-center justify-center p-4">
                                    <div class="relative w-full max-w-5xl max-h-[90vh] overflow-hidden rounded-lg bg-black">
                                        <button type="button" data-route-gallery-close
                                            class="absolute top-3 right-3 z-10 text-white/80 hover:text-white px-3 py-2"
                                            aria-label="Close">✕</button>

                                        <button type="button" data-route-gallery-prev
                                            class="absolute left-2 top-1/2 -translate-y-1/2 z-10 px-3 py-2 text-white/80 hover:text-white"
                                            aria-label="Previous">‹</button>
                                        <button type="button" data-route-gallery-next
                                            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 px-3 py-2 text-white/80 hover:text-white"
                                            aria-label="Next">›</button>

                                        <img data-route-gallery-lightbox-img alt="Route photo"
                                            class="block max-h-[90vh] w-auto max-w-full mx-auto object-contain">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Topo Diagram -->
                    @if($route->topo_url)
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Topo Diagram</h3>
                                <livewire:routes.save-offline :routeId="$route->id" />
                            </div>
                            <div class="w-full rounded-lg border border-gray-300 bg-white p-2">
                                <div class="w-full relative" data-topo-viewer data-topo-url="{{ route('routes.topo', $route) }}">
                                    <script type="application/json" data-topo-data>{!! json_encode($route->topo_data) !!}</script>
                                    <canvas data-topo-canvas></canvas>
                                    <button type="button" data-topo-lightbox-open
                                        class="absolute inset-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        aria-label="Open topo in full screen"></button>
                                    <div data-topo-tooltip class="pointer-events-none hidden absolute z-10 max-w-xs rounded-md bg-gray-900 text-white text-xs px-2 py-1 shadow-lg"></div>

                                    <div data-topo-lightbox class="fixed inset-0 z-50 hidden">
                                        <div data-topo-lightbox-backdrop class="absolute inset-0 bg-black/60"></div>
                                        <div class="absolute inset-0 z-10 flex items-center justify-center p-4">
                                            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-lg bg-white shadow-lg border border-gray-200 p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="text-sm font-semibold text-gray-900">Topo Diagram</div>
                                                    <button type="button" data-topo-lightbox-close
                                                        class="text-gray-500 hover:text-gray-700 px-2 py-1"
                                                        aria-label="Close">✕</button>
                                                </div>
                                                <div class="relative w-full max-h-[78vh]" data-topo-lightbox-wrap>
                                                    <canvas data-topo-lightbox-canvas></canvas>
                                                    <div data-topo-lightbox-tooltip class="pointer-events-none hidden absolute z-10 max-w-sm rounded-md bg-gray-900 text-white text-xs px-2 py-1 shadow-lg"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Location -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Location</h3>
                        <p class="text-sm text-gray-700">
                            <a href="{{ route('locations.show', $route->location) }}"
                                class="text-indigo-600 hover:text-indigo-900">
                                {{ $route->location->getFullPath() }}
                            </a>
                        </p>
                    </div>

                    <!-- Metadata -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created by</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->creator->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created on</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $route->created_at->format('M d, Y') }}</dd>
                            </div>
                            @if($route->is_approved && $route->approver)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Approved by</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $route->approver->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Approved on</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $route->approved_at->format('M d, Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Ratings Section -->
            <div class="mt-8 bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Community Rating</h3>

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        @if($route->ratings->count() > 0)
                            <div class="text-3xl font-bold text-gray-900">
                                {{ $route->getPositiveRatingPercentage() }}%
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $route->ratings->where('is_positive', true)->count() }} 👍 /
                                {{ $route->ratings->where('is_positive', false)->count() }} 👎
                                <div class="text-xs text-gray-500">{{ $route->ratings->count() }} total ratings</div>
                            </div>
                        @else
                            <div class="text-gray-500">No ratings yet</div>
                        @endif
                    </div>

                    @auth
                        @if($userRating)
                            <div class="text-sm text-gray-600">
                                You rated: {{ $userRating->is_positive ? '👍 Positive' : '👎 Negative' }}
                            </div>
                        @elseif($userHasAscent)
                            <form action="{{ route('routes.rate', $route) }}" method="POST" class="flex space-x-2">
                                @csrf
                                <button type="submit" name="is_positive" value="1"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                    👍 Positive
                                </button>
                                <button type="submit" name="is_positive" value="0"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                    👎 Negative
                                </button>
                            </form>
                        @else
                            <div class="text-sm text-gray-500">
                                Log an ascent to rate this route
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Comments Section -->
            <div class="mt-8 bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Comments ({{ $route->comments->count() }})
                </h3>

                @auth
                    <!-- Comment Form -->
                    <form action="{{ route('routes.comments.store', $route) }}" method="POST" enctype="multipart/form-data" class="mb-6">
                        @csrf
                        <textarea name="content" rows="3" required
                            placeholder="Share your experience, beta, or ask questions..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Attach Photo (optional)</label>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                            Post Comment
                        </button>
                    </form>
                @else
                    <p class="mb-6 text-gray-600">
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-900">Login</a> to comment
                    </p>
                @endauth

                <!-- Comments List -->
                <div class="space-y-6">
                    @forelse($comments as $comment)
                        @include('routes.partials.comment', ['comment' => $comment, 'level' => 0])
                    @empty
                        <p class="text-gray-500 text-center py-4">No comments yet. Be the first to comment!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

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

    <div class="py-12">
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
                    <!-- Approval Status -->
                    @if(!$route->is_approved)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        This route is pending approval.
                                        @can('approve', $route)
                                            <span class="ml-2">
                                                <form action="{{ route('routes.approve', $route) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-yellow-700 underline hover:text-yellow-600">
                                                        Approve Now
                                                    </button>
                                                </form>
                                                |
                                                <form action="{{ route('routes.reject', $route) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this route?');">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-yellow-700 underline hover:text-yellow-600">
                                                        Reject
                                                    </button>
                                                </form>
                                            </span>
                                        @endcan
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

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
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Topo Diagram -->
                    @if($route->topo_url)
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Topo Diagram</h3>
                            <div class="w-full rounded-lg border border-gray-300 bg-white p-2">
                                <div class="w-full" data-topo-viewer data-topo-url="{{ route('routes.topo', $route) }}">
                                    <script type="application/json" data-topo-data>{!! json_encode($route->topo_data) !!}</script>
                                    <canvas data-topo-canvas></canvas>
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

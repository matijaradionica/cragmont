<div class="space-y-6">
    <!-- Search and Filter Section -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Search routes...">
            </div>

            <!-- Location Filter -->
            <div>
                <label for="locationId" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select id="locationId" wire:model.live="locationId"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Locations</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">
                            {{ str_repeat('—', $location->level) }} {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Route Type Filter -->
            <div>
                <label for="routeType" class="block text-sm font-medium text-gray-700 mb-1">Route Type</label>
                <select id="routeType" wire:model.live="routeType"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="Alpine">Alpine</option>
                    <option value="Sport">Sport</option>
                    <option value="Traditional">Traditional</option>
                </select>
            </div>

            <!-- Grade Type Filter -->
            <div>
                <label for="gradeType" class="block text-sm font-medium text-gray-700 mb-1">Grade System</label>
                <select id="gradeType" wire:model.live="gradeType"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Systems</option>
                    <option value="UIAA">UIAA</option>
                    <option value="French">French</option>
                </select>
            </div>

            <!-- Min Grade -->
            @if($gradeType)
                <div>
                    <label for="minGrade" class="block text-sm font-medium text-gray-700 mb-1">Min Grade</label>
                    <input type="text" id="minGrade" wire:model.live="minGrade"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="e.g., 5+ or 6a">
                </div>

                <!-- Max Grade -->
                <div>
                    <label for="maxGrade" class="block text-sm font-medium text-gray-700 mb-1">Max Grade</label>
                    <input type="text" id="maxGrade" wire:model.live="maxGrade"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="e.g., 7- or 7b">
                </div>
            @endif

            <!-- Admin: Show Pending Only -->
            @can('moderate')
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="showPendingOnly"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Pending Only</span>
                    </label>
                </div>
            @endcan
        </div>

        <!-- Reset Filters Button -->
        <div class="mt-4">
            <button wire:click="resetFilters" type="button"
                class="text-sm text-indigo-600 hover:text-indigo-900">
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Results Section -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        @if($routes->isEmpty())
            <div class="p-6 text-center text-gray-500">
                No routes found matching your criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($routes as $route)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('routes.show', $route) }}'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $route->name }}</div>
                                    @if(!$route->is_approved)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $route->location->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $route->grade_type }}: {{ $route->grade_value }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $route->route_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($route->status === 'New') bg-blue-100 text-blue-800
                                        @elseif($route->status === 'Equipped') bg-green-100 text-green-800
                                        @elseif($route->status === 'Needs Repair') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $route->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $route->creator->name }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $routes->links() }}
            </div>
        @endif
    </div>
</div>

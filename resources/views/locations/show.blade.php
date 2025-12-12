<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $location->getFullPath() }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $location)
                    <a href="{{ route('locations.edit', $location) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Edit
                    </a>
                @endcan
                @can('delete', $location)
                    <form action="{{ route('locations.destroy', $location) }}" method="POST" onsubmit="return confirm('Are you sure? This will also delete all child locations and routes.');">
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Location Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Level</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($location->level === 0) Mountain / Area
                            @elseif($location->level === 1) Cliff
                            @else Sector
                            @endif
                        </dd>
                    </div>
                    @if($location->parent)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Parent Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('locations.show', $location->parent) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $location->parent->name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($location->gps_lat && $location->gps_lng)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">GPS Coordinates</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $location->gps_lat }}, {{ $location->gps_lng }}
                            </dd>
                        </div>
                    @endif
                </dl>
                @if($location->description)
                    <div class="mt-4">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $location->description }}</dd>
                    </div>
                @endif
            </div>

            <!-- Child Locations -->
            @if($location->children->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sub-locations</h3>
                    <ul class="divide-y divide-gray-200">
                        @foreach($location->children as $child)
                            <li class="py-3">
                                <a href="{{ route('locations.show', $child) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $child->name }}
                                </a>
                                @if($child->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $child->description }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Routes -->
            @if($location->routes->isNotEmpty())
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Routes at this Location</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($location->routes as $route)
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

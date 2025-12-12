<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Routes</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_routes'] }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-semibold text-green-600">{{ $stats['approved_routes'] }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Routes</div>
                    <div class="mt-2 text-3xl font-semibold text-yellow-600">{{ $stats['pending_routes'] }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Reports</div>
                    <div class="mt-2 text-3xl font-semibold text-orange-600">{{ $stats['pending_reports'] }}</div>
                    <a href="{{ route('admin.reports.index') }}" class="mt-2 text-xs text-indigo-600 hover:text-indigo-900">View Reports →</a>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Locations</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_locations'] }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Users</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_users'] }}</div>
                </div>
            </div>

            <!-- Pending Routes -->
            @if($pendingRoutes->isNotEmpty())
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Pending Routes</h3>
                        <form action="{{ route('admin.routes.bulk-approve') }}" method="POST" id="bulk-approve-form">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                                onclick="return confirm('Approve all selected routes?')">
                                Approve Selected
                            </button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="select-all" onclick="toggleAll(this)"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingRoutes as $route)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="route_ids[]" value="{{ $route->id }}" form="bulk-approve-form"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('routes.show', $route) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $route->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $route->location->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $route->grade_type }}: {{ $route->grade_value }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $route->creator->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $route->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <form action="{{ route('routes.approve', $route) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                            </form>
                                            <form action="{{ route('routes.reject', $route) }}" method="POST" class="inline" onsubmit="return confirm('Reject this route?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $pendingRoutes->links() }}
                    </div>
                </div>

                <script>
                    function toggleAll(source) {
                        const checkboxes = document.querySelectorAll('input[name="route_ids[]"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = source.checked;
                        });
                    }
                </script>
            @else
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-center text-gray-500">No pending routes. All caught up!</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

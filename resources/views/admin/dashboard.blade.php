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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Routes</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_routes'] }}</div>
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

            <!-- Quick Links -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('admin.users.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition">
                        <div class="font-medium text-gray-900">Manage Users</div>
                        <div class="text-sm text-gray-500 mt-1">View and update user roles</div>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition">
                        <div class="font-medium text-gray-900">Comment Reports</div>
                        <div class="text-sm text-gray-500 mt-1">Review reported comments</div>
                    </a>
                    <a href="{{ route('admin.condition-reports.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition">
                        <div class="font-medium text-gray-900">Condition Reports</div>
                        <div class="text-sm text-gray-500 mt-1">Moderate route conditions</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

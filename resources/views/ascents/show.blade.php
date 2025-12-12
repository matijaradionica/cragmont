<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ascent Details') }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $ascent)
                    <a href="{{ route('ascents.edit', $ascent) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Edit
                    </a>
                @endcan
                @can('delete', $ascent)
                    <form action="{{ route('ascents.destroy', $ascent) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this ascent?');">
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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Route Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Route</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900">
                            <a href="{{ route('routes.show', $ascent->route) }}" class="hover:text-indigo-600">
                                {{ $ascent->route->name }}
                            </a>
                        </h4>
                        <p class="text-gray-600 mt-1">
                            {{ $ascent->route->location->getFullPath() }}
                        </p>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-medium">
                            {{ $ascent->route->grade_type }}: {{ $ascent->route->grade_value }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-medium">
                            {{ $ascent->route->route_type }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-medium">
                            {{ $ascent->route->length_m }}m
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ascent Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ascent Details</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-medium">
                            {{ $ascent->ascent_date->format('F j, Y') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ascent->getStatusBadgeClass() }}">
                                {{ $ascent->status }}
                            </span>
                        </dd>
                    </div>

                    @if($ascent->partners)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Climbing Partners</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ascent->partners }}</dd>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Climber</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ascent->user->name }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Logged on</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ascent->created_at->format('F j, Y \a\t g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Notes / Impression -->
            @if($ascent->notes)
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Notes</h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line">{{ $ascent->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Back to Logbook -->
            <div class="flex justify-between items-center">
                <a href="{{ route('ascents.index') }}"
                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Logbook
                </a>
                <a href="{{ route('routes.show', $ascent->route) }}"
                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                    View Route Details
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

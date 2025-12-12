<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Locations') }}
            </h2>
            @can('create', App\Models\Location::class)
                <a href="{{ route('locations.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Add New Location
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    @foreach($locations as $location)
                        <div class="mb-6 border-l-4 border-indigo-500 pl-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <a href="{{ route('locations.show', $location) }}" class="hover:text-indigo-600">
                                    {{ $location->name }}
                                </a>
                            </h3>
                            @if($location->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $location->description }}</p>
                            @endif

                            @if($location->children->isNotEmpty())
                                <div class="mt-3 ml-4 space-y-2">
                                    @foreach($location->children as $child)
                                        <div class="border-l-2 border-gray-300 pl-4">
                                            <h4 class="font-medium text-gray-900">
                                                <a href="{{ route('locations.show', $child) }}" class="hover:text-indigo-600">
                                                    {{ $child->name }}
                                                </a>
                                            </h4>
                                            @if($child->children->isNotEmpty())
                                                <div class="mt-2 ml-4 text-sm text-gray-600">
                                                    @foreach($child->children as $sector)
                                                        <a href="{{ route('locations.show', $sector) }}" class="hover:text-indigo-600">
                                                            {{ $sector->name }}
                                                        </a>@if(!$loop->last), @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($locations->isEmpty())
                        <p class="text-center text-gray-500">No locations found. Create one to get started!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

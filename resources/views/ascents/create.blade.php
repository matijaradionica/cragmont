<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log New Ascent') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                @if(isset($route) && $route)
                    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <h3 class="font-semibold text-indigo-900">Logging ascent for:</h3>
                        <p class="text-lg font-medium text-indigo-800 mt-1">{{ $route->name }}</p>
                        <p class="text-sm text-indigo-600">{{ $route->location->getFullPath() }} • {{ $route->grade_type }}: {{ $route->grade_value }}</p>
                    </div>
                @endif

                <form action="{{ route('ascents.store') }}" method="POST">
                    @csrf
                    @include('ascents._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

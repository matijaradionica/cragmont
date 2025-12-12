<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Location') }}: {{ $location->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('locations.update', $location) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('locations._form', ['location' => $location])

                    <div class="flex items-center justify-end mt-6 space-x-3">
                        <a href="{{ route('locations.show', $location) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Update Location
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

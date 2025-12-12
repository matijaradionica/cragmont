<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Ascent') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('ascents.update', $ascent) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('ascents._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

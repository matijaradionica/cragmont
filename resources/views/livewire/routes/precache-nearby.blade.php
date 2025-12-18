<?php

use Livewire\Volt\Component;

new class extends Component
{
    public int $radius = 10;
    public int $limit = 20;
}; ?>

<div
    class="flex flex-col gap-3"
    x-data="{
        online: navigator.onLine,
        precaching: false,
        progress: { current: 0, total: 0 },
        results: null,
        error: null,
        showSettings: false,
    }"
    x-on:cragmont:online.window="online = true"
    x-on:cragmont:offline.window="online = false"
    x-on:cragmont:precache-location-acquired.window="precaching = true; error = null"
    x-on:cragmont:precache-progress.window="progress = $event.detail"
    x-on:cragmont:precache-complete.window="precaching = false; results = $event.detail; progress = { current: 0, total: 0 }"
    x-on:cragmont:precache-failed.window="precaching = false; error = $event.detail.message; progress = { current: 0, total: 0 }"
>
    <div class="flex items-center gap-3">
        <button
            type="button"
            x-on:click="window.dispatchEvent(new CustomEvent('cragmont:precache-nearby', { detail: { radius: {{ $radius }}, limit: {{ $limit }} } })); results = null; error = null;"
            x-bind:disabled="!online || precaching"
            class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-widest transition
                bg-green-600 text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
        >
            <svg x-show="!precaching" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <svg x-cloak x-show="precaching" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!precaching">Save Nearby Routes</span>
            <span x-cloak x-show="precaching">Saving...</span>
        </button>

        <button
            type="button"
            x-on:click="showSettings = !showSettings"
            x-bind:disabled="precaching"
            class="inline-flex items-center px-2 py-1.5 rounded-md text-xs transition
                bg-gray-200 text-gray-700 hover:bg-gray-300 disabled:opacity-60 disabled:cursor-not-allowed"
            x-bind:title="showSettings ? 'Hide Settings' : 'Show Settings'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </button>
    </div>

    <div x-cloak x-show="showSettings" class="bg-gray-50 p-3 rounded-md space-y-2">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Radius (km)</label>
            <input
                type="number"
                wire:model.live="radius"
                min="1"
                max="100"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Max Routes</label>
            <input
                type="number"
                wire:model.live="limit"
                min="1"
                max="50"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>
    </div>

    <div x-cloak x-show="!online" class="text-xs text-gray-500">
        Go online to cache nearby routes.
    </div>

    <div x-cloak x-show="precaching && progress.total > 0" class="text-xs text-blue-700">
        Saving <span x-text="progress.current"></span> of <span x-text="progress.total"></span> routes...
    </div>

    <div x-cloak x-show="results && results.succeeded.length > 0" class="text-xs text-green-700">
        Saved <span x-text="results.succeeded.length"></span> nearby route(s) for offline use.
        <span x-show="results.failed.length > 0" class="text-orange-600">
            (<span x-text="results.failed.length"></span> failed)
        </span>
    </div>

    <div x-cloak x-show="results && results.succeeded.length === 0 && results.failed.length === 0" class="text-xs text-gray-600">
        <span x-text="results.message || 'No nearby routes found with topo diagrams.'"></span>
    </div>

    <div x-cloak x-show="error" class="text-xs text-red-700" x-text="error"></div>
</div>

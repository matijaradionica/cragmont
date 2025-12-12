<?php

use App\Models\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public int $routeId;

    public function send(): void
    {
        $route = Route::findOrFail($this->routeId);
        $this->authorize('view', $route);

        $this->dispatch('cragmont:offline-route-data', routeId: (string) $route->id, topoUrl: $route->topo_url ? route('routes.topo', $route) : null, topoData: $route->topo_data);
    }
}; ?>

<div
    class="flex items-center gap-3"
    x-data="{ online: navigator.onLine, saving: false, saved: false, error: null }"
    x-on:cragmont:online.window="online = true"
    x-on:cragmont:offline.window="online = false"
    x-on:cragmont:offline-saved.window="if ($event.detail.routeId === '{{ $routeId }}') { saving = false; saved = true; error = null; }"
    x-on:cragmont:offline-save-failed.window="if ($event.detail.routeId === '{{ $routeId }}') { saving = false; saved = false; error = $event.detail.message; }"
>
    <button
        type="button"
        wire:click="send"
        x-on:click="saving = true; saved = false; error = null"
        x-bind:disabled="!online || saving"
        class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-widest transition
            bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed"
    >
        <span x-show="!saving">Save for Offline</span>
        <span x-cloak x-show="saving">Saving...</span>
    </button>

    <span x-cloak x-show="!online" class="text-xs text-gray-500">Go online to save.</span>
    <span x-cloak x-show="saved" class="text-xs text-green-700">Saved.</span>
    <span x-cloak x-show="error" class="text-xs text-red-700" x-text="error"></span>
</div>


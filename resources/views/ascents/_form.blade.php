<div class="space-y-6">
    <!-- Route Selection -->
    <div>
        <label for="route_id" class="block text-sm font-medium text-gray-700">Route *</label>
        <select name="route_id" id="route_id" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            {{ isset($route) && $route ? 'disabled' : '' }}>
            <option value="">Select a route...</option>
            @foreach($routes as $r)
                <option value="{{ $r->id }}"
                    {{ (isset($route) && $route && $route->id == $r->id) || (old('route_id', $ascent->route_id ?? '') == $r->id) ? 'selected' : '' }}>
                    {{ $r->name }} - {{ $r->location->getFullPath() }} ({{ $r->grade_type }}: {{ $r->grade_value }})
                </option>
            @endforeach
        </select>
        @if(isset($route) && $route)
            <input type="hidden" name="route_id" value="{{ $route->id }}">
        @endif
        @error('route_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Ascent Date -->
    <div>
        <label for="ascent_date" class="block text-sm font-medium text-gray-700">Date *</label>
        <input type="date" name="ascent_date" id="ascent_date" required
            max="{{ date('Y-m-d') }}"
            value="{{ old('ascent_date', isset($ascent) ? $ascent->ascent_date->format('Y-m-d') : date('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('ascent_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
        <select name="status" id="status" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="Success" {{ old('status', $ascent->status ?? 'Success') == 'Success' ? 'selected' : '' }}>
                ✓ Success (Completed)
            </option>
            <option value="Attempt" {{ old('status', $ascent->status ?? '') == 'Attempt' ? 'selected' : '' }}>
                ↻ Attempt (Working on it)
            </option>
            <option value="Failed" {{ old('status', $ascent->status ?? '') == 'Failed' ? 'selected' : '' }}>
                ✗ Failed (Did not complete)
            </option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Partners -->
    <div>
        <label for="partners" class="block text-sm font-medium text-gray-700">
            Climbing Partners
            <span class="text-gray-400 font-normal">(optional)</span>
        </label>
        <input type="text" name="partners" id="partners"
            value="{{ old('partners', $ascent->partners ?? '') }}"
            placeholder="e.g., John, Sarah"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <p class="mt-1 text-sm text-gray-500">Names of people you climbed with</p>
        @error('partners')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Notes -->
    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">
            Personal Notes / Impression
            <span class="text-gray-400 font-normal">(optional)</span>
        </label>
        <textarea name="notes" id="notes" rows="6"
            placeholder="How did it go? Conditions, beta, what worked well, what to improve next time..."
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $ascent->notes ?? '') }}</textarea>
        <p class="mt-1 text-sm text-gray-500">Max 2000 characters</p>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-4 border-t">
        <a href="{{ route('ascents.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
            Cancel
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            {{ isset($ascent) ? 'Update Ascent' : 'Log Ascent' }}
        </button>
    </div>
</div>

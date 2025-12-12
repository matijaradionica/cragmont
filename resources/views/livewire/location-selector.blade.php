<div class="space-y-4">
    <!-- Hidden input for the actual location_id value -->
    <input type="hidden" name="location_id" value="{{ $locationId }}">

    <!-- Mountain Selection (Level 0) -->
    <div>
        <label for="selectedMountain" class="block text-sm font-medium text-gray-700 mb-1">
            Mountain / Area <span class="text-red-500">*</span>
        </label>
        <select id="selectedMountain" wire:model.live="selectedMountain" required
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select a mountain/area...</option>
            @foreach($mountains as $mountain)
                <option value="{{ $mountain->id }}">{{ $mountain->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Cliff Selection (Level 1) -->
    @if($selectedMountain && $cliffs->isNotEmpty())
        <div>
            <label for="selectedCliff" class="block text-sm font-medium text-gray-700 mb-1">
                Cliff (Optional)
            </label>
            <select id="selectedCliff" wire:model.live="selectedCliff"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a cliff (optional)...</option>
                @foreach($cliffs as $cliff)
                    <option value="{{ $cliff->id }}">{{ $cliff->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <!-- Sector Selection (Level 2) -->
    @if($selectedCliff && $sectors->isNotEmpty())
        <div>
            <label for="selectedSector" class="block text-sm font-medium text-gray-700 mb-1">
                Sector (Optional)
            </label>
            <select id="selectedSector" wire:model.live="selectedSector"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a sector (optional)...</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @error('location_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

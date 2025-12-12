<div class="space-y-6">
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Location Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $location->name) }}" required
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Parent Location -->
    <div>
        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
            Parent Location (Optional)
        </label>
        <select name="parent_id" id="parent_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">None (Top Level)</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ old('parent_id', $location->parent_id) == $loc->id ? 'selected' : '' }}>
                    {{ str_repeat('—', $loc->level) }} {{ $loc->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- GPS Coordinates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="gps_lat" class="block text-sm font-medium text-gray-700 mb-1">
                Latitude
            </label>
            <input type="number" step="0.00000001" name="gps_lat" id="gps_lat" value="{{ old('gps_lat', $location->gps_lat) }}"
                placeholder="e.g., 37.8651"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('gps_lat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="gps_lng" class="block text-sm font-medium text-gray-700 mb-1">
                Longitude
            </label>
            <input type="number" step="0.00000001" name="gps_lng" id="gps_lng" value="{{ old('gps_lng', $location->gps_lng) }}"
                placeholder="e.g., -119.5383"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('gps_lng')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Description
        </label>
        <textarea name="description" id="description" rows="4"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Describe this location...">{{ old('description', $location->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="space-y-6">
    <!-- Route Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Route Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $route->name) }}" required
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Location Selector (Livewire Component) -->
    <div>
        <livewire:location-selector :locationId="old('location_id', $route->location_id)" />
        @error('location_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Technical Specifications Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Grade Type -->
        <div>
            <label for="grade_type" class="block text-sm font-medium text-gray-700 mb-1">
                Grade System <span class="text-red-500">*</span>
            </label>
            <select name="grade_type" id="grade_type" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select grade system...</option>
                <option value="UIAA" {{ old('grade_type', $route->grade_type) === 'UIAA' ? 'selected' : '' }}>UIAA</option>
                <option value="French" {{ old('grade_type', $route->grade_type) === 'French' ? 'selected' : '' }}>French</option>
            </select>
            @error('grade_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Grade Value -->
        <div>
            <label for="grade_value" class="block text-sm font-medium text-gray-700 mb-1">
                Grade <span class="text-red-500">*</span>
            </label>
            <input type="text" name="grade_value" id="grade_value" value="{{ old('grade_value', $route->grade_value) }}" required
                placeholder="e.g., 5+, 6a, 7b+"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('grade_value')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Route Type -->
        <div>
            <label for="route_type" class="block text-sm font-medium text-gray-700 mb-1">
                Route Type <span class="text-red-500">*</span>
            </label>
            <select name="route_type" id="route_type" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select type...</option>
                <option value="Alpine" {{ old('route_type', $route->route_type) === 'Alpine' ? 'selected' : '' }}>Alpine</option>
                <option value="Sport" {{ old('route_type', $route->route_type) === 'Sport' ? 'selected' : '' }}>Sport</option>
                <option value="Traditional" {{ old('route_type', $route->route_type) === 'Traditional' ? 'selected' : '' }}>Traditional</option>
            </select>
            @error('route_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Risk Rating -->
        <div>
            <label for="risk_rating" class="block text-sm font-medium text-gray-700 mb-1">
                Risk Rating <span class="text-red-500">*</span>
            </label>
            <select name="risk_rating" id="risk_rating" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="None" {{ old('risk_rating', $route->risk_rating) === 'None' ? 'selected' : '' }}>None</option>
                <option value="R" {{ old('risk_rating', $route->risk_rating) === 'R' ? 'selected' : '' }}>R (Runout)</option>
                <option value="X" {{ old('risk_rating', $route->risk_rating) === 'X' ? 'selected' : '' }}>X (Serious)</option>
            </select>
            @error('risk_rating')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Length -->
        <div>
            <label for="length_m" class="block text-sm font-medium text-gray-700 mb-1">
                Length (meters)
            </label>
            <input type="number" name="length_m" id="length_m" value="{{ old('length_m', $route->length_m) }}"
                min="1" step="1"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('length_m')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Pitch Count -->
        <div>
            <label for="pitch_count" class="block text-sm font-medium text-gray-700 mb-1">
                Number of Pitches <span class="text-red-500">*</span>
            </label>
            <input type="number" name="pitch_count" id="pitch_count" value="{{ old('pitch_count', $route->pitch_count ?? 1) }}" required
                min="1" max="50"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('pitch_count')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Status -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status" id="status" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="New" {{ old('status', $route->status) === 'New' ? 'selected' : '' }}>New</option>
                <option value="Equipped" {{ old('status', $route->status) === 'Equipped' ? 'selected' : '' }}>Equipped</option>
                <option value="Needs Repair" {{ old('status', $route->status) === 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                <option value="Closed" {{ old('status', $route->status) === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Approach Description -->
    <div>
        <label for="approach_description" class="block text-sm font-medium text-gray-700 mb-1">
            Approach Description
        </label>
        <textarea name="approach_description" id="approach_description" rows="4"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Describe how to reach the route from the parking area or trailhead...">{{ old('approach_description', $route->approach_description) }}</textarea>
        @error('approach_description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Descent Description -->
    <div>
        <label for="descent_description" class="block text-sm font-medium text-gray-700 mb-1">
            Descent Description
        </label>
        <textarea name="descent_description" id="descent_description" rows="4"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Describe how to descend from the route...">{{ old('descent_description', $route->descent_description) }}</textarea>
        @error('descent_description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Required Gear -->
    <div>
        <label for="required_gear" class="block text-sm font-medium text-gray-700 mb-1">
            Required Gear
        </label>
        <textarea name="required_gear" id="required_gear" rows="4"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="List the gear needed for this route (e.g., 12 quickdraws, cams to #3, 60m rope)...">{{ old('required_gear', $route->required_gear) }}</textarea>
        @error('required_gear')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Topo Upload (Livewire Component) -->
    <div>
        <livewire:topo-upload :existingTopoUrl="$route->topo_url" />
        @error('topo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Location::class);

        // Get top-level locations (mountains) with their children loaded
        $locations = Location::topLevel()
            ->with(['children.children']) // Load 2 levels deep
            ->orderBy('name')
            ->get();

        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Location::class);

        // Get all locations for parent selector
        $locations = Location::orderBy('level')->orderBy('name')->get();

        return view('locations.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request)
    {
        $validated = $request->validated();

        $location = Location::create($validated);

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('locations.show', $location)
            ->with('success', 'Location created successfully!')
            ->header('X-Livewire-Navigate', 'false');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        $this->authorize('view', $location);

        // Load relationships
        $location->load(['parent', 'children', 'routes' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        $this->authorize('update', $location);

        // Get all locations except current and its descendants (to prevent circular references)
        $descendantIds = $location->children()->pluck('id')->toArray();
        $descendantIds[] = $location->id;

        $locations = Location::whereNotIn('id', $descendantIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('locations.edit', compact('location', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $validated = $request->validated();

        $location->update($validated);

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('locations.show', $location)
            ->with('success', 'Location updated successfully!')
            ->header('X-Livewire-Navigate', 'false');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);

        $location->delete();

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('locations.index')
            ->with('success', 'Location deleted successfully.')
            ->header('X-Livewire-Navigate', 'false');
    }
}

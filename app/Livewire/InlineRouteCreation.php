<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Photo;
use App\Models\Route;
use Livewire\Component;
use Livewire\WithFileUploads;

class InlineRouteCreation extends Component
{
    use WithFileUploads;

    public $showModal = false;

    public $locationSearch = '';

    public $filteredLocations = [];

    public $selectedLocationId = null;

    // Route fields
    public $name = '';

    public $location_id = null;

    public $grade_type = '';

    public $grade_value = '';

    public $route_type = '';

    public $risk_rating = 'None';

    public $pitch_count = 1;

    public $length_m = null;

    public $status = 'New';

    public $approach_description = '';

    public $descent_description = '';

    public $required_gear = '';

    public $topo = null;

    public $topo_data = null;

    public $photos = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'location_id' => 'required|exists:locations,id',
        'grade_type' => 'required|in:UIAA,French',
        'grade_value' => 'required|string|max:10',
        'route_type' => 'required|in:Alpine,Sport,Traditional',
        'risk_rating' => 'required|in:None,R,X',
        'pitch_count' => 'required|integer|min:1|max:50',
        'length_m' => 'nullable|integer|min:1|max:10000',
        'status' => 'required|in:New,Equipped,Needs Repair,Closed',
        'approach_description' => 'nullable|string|max:5000',
        'descent_description' => 'nullable|string|max:5000',
        'required_gear' => 'nullable|string|max:2000',
        'topo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
    ];

    public function mount()
    {
        $this->filteredLocations = Location::orderBy('name')->limit(10)->get();
    }

    public function updatedLocationSearch()
    {
        if (strlen($this->locationSearch) >= 2) {
            $this->filteredLocations = Location::where('name', 'like', '%'.$this->locationSearch.'%')
                ->orderBy('name')
                ->limit(20)
                ->get();
        } else {
            $this->filteredLocations = Location::orderBy('name')->limit(10)->get();
        }
    }

    public function selectLocation($locationId)
    {
        $location = Location::find($locationId);
        if ($location) {
            $this->location_id = $locationId;
            $this->locationSearch = $location->getFullPath();
            $this->filteredLocations = [];
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset([
            'name', 'location_id', 'grade_type', 'grade_value', 'route_type',
            'risk_rating', 'pitch_count', 'length_m', 'status',
            'approach_description', 'descent_description', 'required_gear',
            'topo', 'topo_data', 'photos', 'locationSearch',
        ]);
        $this->filteredLocations = Location::orderBy('name')->limit(10)->get();
    }

    public function createRoute()
    {
        $this->validate();

        // Create route with auto-approval (as per requirement #3: Option B)
        $routeData = [
            'name' => $this->name,
            'location_id' => $this->location_id,
            'grade_type' => $this->grade_type,
            'grade_value' => $this->grade_value,
            'route_type' => $this->route_type,
            'risk_rating' => $this->risk_rating,
            'pitch_count' => $this->pitch_count,
            'length_m' => $this->length_m,
            'status' => $this->status,
            'approach_description' => $this->approach_description,
            'descent_description' => $this->descent_description,
            'required_gear' => $this->required_gear,
            'created_by_user_id' => auth()->id(),
            // Auto-approve routes created during ascent logging
            'is_approved' => true,
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
        ];

        // Handle topo file upload
        if ($this->topo) {
            $path = $this->topo->store('topos', 'public');
            $routeData['topo_url'] = $path;
        }

        $route = Route::create($routeData);

        // Handle photo uploads
        if ($this->photos && count($this->photos) > 0) {
            if (count($this->photos) > 10) {
                $this->addError('photos', 'You can upload a maximum of 10 photos per route.');

                return;
            }

            foreach (array_values($this->photos) as $index => $file) {
                $path = $file->store('photos', 'public');
                Photo::create([
                    'route_id' => $route->id,
                    'user_id' => auth()->id(),
                    'path' => $path,
                    'is_topo' => false,
                    'order' => $index,
                ]);
            }
        }

        // Emit event to parent component with the created route
        $this->dispatch('routeCreated', routeId: $route->id, routeName: $route->name);

        // Close modal and reset form
        $this->closeModal();

        session()->flash('success', 'Route created successfully and auto-approved!');
    }

    public function render()
    {
        return view('livewire.inline-route-creation');
    }
}

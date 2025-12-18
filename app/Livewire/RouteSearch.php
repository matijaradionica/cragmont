<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

class RouteSearch extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $locationId = '';
    public $routeType = '';
    public $gradeType = '';
    public $minGrade = '';
    public $maxGrade = '';
    public $statuses = [];

    /**
     * Mount the component and ensure fresh data.
     */
    public function mount()
    {
        // Clear any Livewire cache to ensure fresh data
        $this->resetPage();
    }

    // Query string parameters for shareable URLs
    protected $queryString = [
        'search' => ['except' => ''],
        'locationId' => ['except' => ''],
        'routeType' => ['except' => ''],
        'gradeType' => ['except' => ''],
        'minGrade' => ['except' => ''],
        'maxGrade' => ['except' => ''],
        'statuses' => ['except' => []],
    ];

    /**
     * Reset pagination when search/filters change.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLocationId()
    {
        $this->resetPage();
    }

    public function updatingRouteType()
    {
        $this->resetPage();
    }

    public function updatingGradeType()
    {
        $this->resetPage();
    }

    /**
     * Reset all filters.
     */
    public function resetFilters()
    {
        $this->reset([
            'search',
            'locationId',
            'routeType',
            'gradeType',
            'minGrade',
            'maxGrade',
            'statuses',
        ]);
        $this->resetPage();
    }

    /**
     * Render the component with filtered routes.
     */
    public function render()
    {
        $query = Route::query()->with(['location', 'creator']);

        // Apply search
        if ($this->search) {
            $query->search($this->search);
        }

        // Apply location filter
        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        // Apply route type filter
        if ($this->routeType) {
            $query->byType($this->routeType);
        }

        // Apply grade filters
        if ($this->gradeType) {
            $query->where('grade_type', $this->gradeType);

            if ($this->minGrade) {
                $query->where('grade_value', '>=', $this->minGrade);
            }

            if ($this->maxGrade) {
                $query->where('grade_value', '<=', $this->maxGrade);
            }
        }

        // Apply status filters
        if (!empty($this->statuses)) {
            $query->whereIn('status', $this->statuses);
        }

        // Order by creation date (newest first) by default, then by name
        $query->orderBy('created_at', 'desc')->orderBy('name');

        // Get paginated results
        $routes = $query->paginate(15);

        // Get all locations for filter dropdown
        $locations = Location::orderBy('level')->orderBy('name')->get();

        return view('livewire.route-search', [
            'routes' => $routes,
            'locations' => $locations,
        ]);
    }
}

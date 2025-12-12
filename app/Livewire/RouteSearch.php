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
    public $showPendingOnly = false;

    // Query string parameters for shareable URLs
    protected $queryString = [
        'search' => ['except' => ''],
        'locationId' => ['except' => ''],
        'routeType' => ['except' => ''],
        'gradeType' => ['except' => ''],
        'minGrade' => ['except' => ''],
        'maxGrade' => ['except' => ''],
        'statuses' => ['except' => []],
        'showPendingOnly' => ['except' => false],
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
            'showPendingOnly',
        ]);
        $this->resetPage();
    }

    /**
     * Render the component with filtered routes.
     */
    public function render()
    {
        $query = Route::query()->with(['location', 'creator']);

        // Show approved routes by default unless user is admin/moderator
        $user = auth()->user();
        if ($this->showPendingOnly && ($user && ($user->isAdmin() || $user->isModerator()))) {
            $query->pending();
        } elseif (!$user || (!$user->isAdmin() && !$user->isModerator())) {
            $query->approved();
        }

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

        // Order by name
        $query->orderBy('name');

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

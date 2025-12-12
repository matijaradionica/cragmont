<?php

namespace App\Livewire;

use App\Models\Location;
use Livewire\Component;

class LocationSelector extends Component
{
    public $selectedLocationId;
    public $selectedMountain = '';
    public $selectedCliff = '';
    public $selectedSector = '';

    // For use in forms - this is the actual location_id value
    public $locationId;

    /**
     * Initialize component with existing location if editing.
     */
    public function mount($locationId = null)
    {
        if ($locationId) {
            $location = Location::find($locationId);

            if ($location) {
                // Set up the cascading dropdowns based on location hierarchy
                switch ($location->level) {
                    case 0: // Mountain
                        $this->selectedMountain = $location->id;
                        break;
                    case 1: // Cliff
                        $this->selectedMountain = $location->parent_id;
                        $this->selectedCliff = $location->id;
                        break;
                    case 2: // Sector
                        $cliff = Location::find($location->parent_id);
                        if ($cliff) {
                            $this->selectedMountain = $cliff->parent_id;
                            $this->selectedCliff = $cliff->id;
                            $this->selectedSector = $location->id;
                        }
                        break;
                }

                $this->locationId = $locationId;
            }
        }
    }

    /**
     * When mountain changes, reset cliff and sector.
     */
    public function updatedSelectedMountain($value)
    {
        $this->selectedCliff = '';
        $this->selectedSector = '';
        $this->updateLocationId();
    }

    /**
     * When cliff changes, reset sector.
     */
    public function updatedSelectedCliff($value)
    {
        $this->selectedSector = '';
        $this->updateLocationId();
    }

    /**
     * When sector changes, update location ID.
     */
    public function updatedSelectedSector($value)
    {
        $this->updateLocationId();
    }

    /**
     * Update the final location_id based on the most specific selection.
     */
    private function updateLocationId()
    {
        if ($this->selectedSector) {
            $this->locationId = $this->selectedSector;
        } elseif ($this->selectedCliff) {
            $this->locationId = $this->selectedCliff;
        } elseif ($this->selectedMountain) {
            $this->locationId = $this->selectedMountain;
        } else {
            $this->locationId = null;
        }

        // Emit event so parent form can capture the value
        $this->dispatch('locationSelected', $this->locationId);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        // Get all mountains (level 0)
        $mountains = Location::topLevel()->orderBy('name')->get();

        // Get cliffs for selected mountain (level 1)
        $cliffs = $this->selectedMountain
            ? Location::where('parent_id', $this->selectedMountain)
                ->where('level', 1)
                ->orderBy('name')
                ->get()
            : collect();

        // Get sectors for selected cliff (level 2)
        $sectors = $this->selectedCliff
            ? Location::where('parent_id', $this->selectedCliff)
                ->where('level', 2)
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.location-selector', [
            'mountains' => $mountains,
            'cliffs' => $cliffs,
            'sectors' => $sectors,
        ]);
    }
}

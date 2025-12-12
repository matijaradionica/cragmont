<?php

namespace App\Livewire;

use App\Models\Location;
use Livewire\Component;

class LocationMap extends Component
{
    public $height = '500px';
    public $centerLat = 37.0902;
    public $centerLng = -95.7129;
    public $zoom = 4;
    public $showSingleLocation = false;
    public $locationId = null;

    public function mount($height = '500px', $locationId = null)
    {
        $this->height = $height;
        $this->locationId = $locationId;

        if ($locationId) {
            $this->showSingleLocation = true;
            $location = Location::find($locationId);

            if ($location && $location->gps_lat && $location->gps_lng) {
                $this->centerLat = $location->gps_lat;
                $this->centerLng = $location->gps_lng;
                $this->zoom = 12;
            }
        }
    }

    public function getLocationsProperty()
    {
        if ($this->showSingleLocation && $this->locationId) {
            $location = Location::with(['routes' => function($query) {
                $query->where('is_approved', true);
            }])->find($this->locationId);

            return $location ? collect([$location]) : collect([]);
        }

        return Location::with(['routes' => function($query) {
            $query->where('is_approved', true);
        }])
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->get();
    }

    public function render()
    {
        return view('livewire.location-map', [
            'locations' => $this->locations,
        ]);
    }
}

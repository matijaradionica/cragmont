<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Yosemite National Park (Mountain - Level 0)
        $yosemite = Location::create([
            'name' => 'Yosemite National Park',
            'gps_lat' => 37.8651,
            'gps_lng' => -119.5383,
            'description' => 'Iconic climbing destination in California',
            'level' => 0,
        ]);

        // Create El Capitan (Cliff - Level 1)
        $elCap = Location::create([
            'name' => 'El Capitan',
            'parent_id' => $yosemite->id,
            'gps_lat' => 37.7342,
            'gps_lng' => -119.6376,
            'description' => 'Massive granite monolith',
            'level' => 1,
        ]);

        // Create sectors under El Capitan (Sector - Level 2)
        Location::create([
            'name' => 'The Nose Sector',
            'parent_id' => $elCap->id,
            'description' => 'Home to the famous Nose route',
            'level' => 2,
        ]);

        Location::create([
            'name' => 'Salathe Wall Sector',
            'parent_id' => $elCap->id,
            'description' => 'Southwest face routes',
            'level' => 2,
        ]);

        // Create Half Dome (Cliff - Level 1)
        $halfDome = Location::create([
            'name' => 'Half Dome',
            'parent_id' => $yosemite->id,
            'gps_lat' => 37.7459,
            'gps_lng' => -119.5332,
            'description' => 'Distinctive granite dome',
            'level' => 1,
        ]);

        Location::create([
            'name' => 'Northwest Face',
            'parent_id' => $halfDome->id,
            'description' => 'Classic multi-pitch routes',
            'level' => 2,
        ]);

        // Create another mountain area
        $joshuaTree = Location::create([
            'name' => 'Joshua Tree National Park',
            'gps_lat' => 33.8734,
            'gps_lng' => -115.9010,
            'description' => 'Desert climbing paradise',
            'level' => 0,
        ]);

        $hiddenValley = Location::create([
            'name' => 'Hidden Valley',
            'parent_id' => $joshuaTree->id,
            'gps_lat' => 33.9912,
            'gps_lng' => -116.1689,
            'description' => 'Popular sport climbing area',
            'level' => 1,
        ]);

        Location::create([
            'name' => 'Intersection Rock',
            'parent_id' => $hiddenValley->id,
            'description' => 'Classic beginner routes',
            'level' => 2,
        ]);
    }
}

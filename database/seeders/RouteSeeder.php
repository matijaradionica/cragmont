<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating routes with factories...');

        // Get all locations (should exist from LocationSeeder)
        $locations = Location::all();

        if ($locations->isEmpty()) {
            $this->command->warn('No locations found. Please run LocationSeeder first.');
            return;
        }

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $this->command->info("Found {$locations->count()} locations and {$users->count()} users");

        // Create a variety of routes
        $routeCount = 0;

        // For each location, create a random number of routes
        foreach ($locations as $location) {
            $numRoutes = match($location->level) {
                0 => rand(0, 2),    // Few routes at mountain level
                1 => rand(1, 3),    // Some routes at cliff level
                2 => rand(2, 4),   // Many routes at sector level
                default => 0,
            };

            for ($i = 0; $i < $numRoutes; $i++) {
                $creator = $users->random();

                $route = Route::factory()
                    ->for($location)
                    ->for($creator, 'creator')
                    ->create();

                $routeCount++;
            }
        }

        $this->command->info("Created {$routeCount} routes");

        // Create some specific high-quality example routes
        $this->createExampleRoutes($locations, $users);

        $this->command->info("Total routes: " . Route::count());
    }

    /**
     * Create some specific example routes for demonstration.
     */
    private function createExampleRoutes($locations, $users): void
    {
        $this->command->info('Creating example showcase routes...');

        $admin = $users->where('role_id', 1)->first();
        $standardUser = $users->where('role_id', 3)->first();

        // Find suitable locations (sectors if available)
        $sectors = $locations->where('level', 2);

        if ($sectors->isEmpty()) {
            $this->command->warn('No sectors found for example routes.');
            return;
        }

        // Create classic beginner route
        Route::factory()
            ->for($sectors->random())
            ->for($admin, 'creator')
            ->create([
                'name' => 'Beginner\'s Luck',
                'grade_type' => 'UIAA',
                'grade_value' => '4',
                'route_type' => 'Sport',
                'pitch_count' => 1,
                'length_m' => 15,
                'status' => 'Equipped',
                'risk_rating' => 'None',
                'approach_description' => 'Walk 2 minutes from parking lot. Route is immediately visible on the right.',
                'descent_description' => 'Lower from fixed anchors with single 60m rope.',
                'required_gear' => '8 quickdraws, 60m rope, helmet recommended',
            ]);

        // Create challenging sport climb
        Route::factory()
            ->for($sectors->random())
            ->for($admin, 'creator')
            ->sport()
            ->create([
                'name' => 'Crimson Wave',
                'grade_type' => 'French',
                'grade_value' => '7a+',
                'pitch_count' => 1,
                'length_m' => 28,
                'status' => 'Equipped',
                'risk_rating' => 'None',
                'approach_description' => '10-minute walk uphill from parking. Follow red markers.',
                'descent_description' => 'Lower from bolted chain anchors.',
                'required_gear' => '14 quickdraws, 70m rope recommended for lowering',
            ]);

        // Create multi-pitch traditional route
        Route::factory()
            ->for($sectors->random())
            ->for($admin, 'creator')
            ->traditional()
            ->create([
                'name' => 'North Ridge Direct',
                'grade_type' => 'UIAA',
                'grade_value' => '6',
                'pitch_count' => 8,
                'length_m' => 250,
                'status' => 'Equipped',
                'risk_rating' => 'R',
                'approach_description' => '30-minute approach with 200m elevation gain. Start at base of obvious buttress.',
                'descent_description' => 'Walk off to the north, following cairned trail back to base.',
                'required_gear' => 'Double rack cams 0.3-3, full set of nuts, 15 slings, 2x60m ropes',
            ]);

        // Create example route from standard user
        if ($standardUser) {
            Route::factory()
                ->for($sectors->random())
                ->for($standardUser, 'creator')
                ->create([
                    'name' => 'Unnamed Project',
                    'grade_type' => 'French',
                    'grade_value' => '8b',
                    'route_type' => 'Sport',
                    'pitch_count' => 1,
                    'length_m' => 35,
                    'status' => 'New',
                    'risk_rating' => 'None',
                    'approach_description' => 'Recently developed route on new wall. 15 min approach.',
                    'descent_description' => 'Lower from new bolted anchors.',
                    'required_gear' => '16 quickdraws, 70m rope essential',
                ]);
        }

        $this->command->info('Example routes created successfully');
    }
}

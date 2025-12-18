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

        // Create exactly 10 routes
        $this->command->info('Creating 10 routes...');

        for ($i = 0; $i < 10; $i++) {
            $location = $locations->random();
            $creator = $users->random();

            Route::factory()
                ->for($location)
                ->for($creator, 'creator')
                ->create();
        }

        $this->command->info("Created 10 routes successfully");
    }
}

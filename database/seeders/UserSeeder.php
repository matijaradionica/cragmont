<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $moderatorRole = Role::where('name', 'Moderator')->first();
        $standardRole = Role::where('name', 'Standard')->first();
        $clubRole = Role::where('name', 'Club/Equipper')->first();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@cragmont.test',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        // Create moderator user
        User::create([
            'name' => 'Moderator User',
            'email' => 'moderator@cragmont.test',
            'password' => Hash::make('password'),
            'role_id' => $moderatorRole->id,
            'email_verified_at' => now(),
        ]);

        // Create standard user
        User::create([
            'name' => 'Standard User',
            'email' => 'user@cragmont.test',
            'password' => Hash::make('password'),
            'role_id' => $standardRole->id,
            'email_verified_at' => now(),
        ]);

        // Create club/equipper user
        User::create([
            'name' => 'Club Equipper',
            'email' => 'club@cragmont.test',
            'password' => Hash::make('password'),
            'role_id' => $clubRole->id,
            'email_verified_at' => now(),
        ]);
    }
}

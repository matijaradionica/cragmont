<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Full system access with all permissions'
            ],
            [
                'name' => 'Moderator',
                'description' => 'Can approve routes and manage locations'
            ],
            [
                'name' => 'Standard',
                'description' => 'Can create routes (requires approval)'
            ],
            [
                'name' => 'Club/Equipper',
                'description' => 'Can create routes (auto-approved)'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}

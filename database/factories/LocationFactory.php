<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Mountain/Area names for level 0
        $mountains = [
            'Red Rock Canyon', 'Smith Rock', 'Devils Tower', 'Eldorado Canyon',
            'Seneca Rocks', 'New River Gorge', 'Rifle Mountain Park', 'Kalymnos',
            'Fontainebleau', 'Ceuse', 'Siurana', 'Margalef', 'Leonidio',
            'Frankenjura', 'Verdon Gorge', 'Paklenica', 'Arco', 'Val di Mello'
        ];

        // Cliff/Crag names for level 1
        $cliffs = [
            'North Face', 'South Wall', 'Main Wall', 'East Buttress', 'West Face',
            'Sentinel Rock', 'Cathedral Wall', 'Black Wall', 'White Cliff', 'Red Wall',
            'Eagle Rock', 'Falcon Crag', 'Thunder Dome', 'Solar Wall', 'Lunar Crag'
        ];

        // Sector names for level 2
        $sectors = [
            'Upper Tier', 'Lower Tier', 'Middle Section', 'Cave Sector', 'Overhanging Wall',
            'Slab Section', 'Pillar Area', 'Corner Zone', 'Arete Section', 'Chimney Area',
            'Roof Sector', 'Hanging Garden', 'Amphitheater', 'Alcove', 'Gallery'
        ];

        $level = fake()->numberBetween(0, 2);

        $names = [
            0 => $mountains,
            1 => $cliffs,
            2 => $sectors,
        ];

        $descriptions = [
            0 => [
                'World-class climbing destination known for diverse routes',
                'Popular climbing area with routes for all abilities',
                'Iconic climbing spot with stunning rock formations',
                'Historic climbing area with classic routes',
                'Beautiful crag featuring excellent rock quality',
                'Renowned for its challenging climbs and scenery',
                'Premier destination for sport and trad climbing',
            ],
            1 => [
                'Impressive cliff face with varied climbing',
                'Steep wall featuring technical routes',
                'Classic crag with multiple pitch options',
                'Popular wall with good protection',
                'Challenging face with sustained climbing',
            ],
            2 => [
                'Well-bolted sector with sport routes',
                'Traditional climbing area with natural protection',
                'Mixed climbing with both sport and trad options',
                'Beginner-friendly sector with easy access',
                'Advanced sector with demanding routes',
            ],
        ];

        return [
            'name' => fake()->randomElement($names[$level]),
            'level' => $level,
            'gps_lat' => $level === 0 ? fake()->latitude(25, 50) : null,
            'gps_lng' => $level === 0 ? fake()->longitude(-125, -70) : null,
            'description' => fake()->randomElement($descriptions[$level]),
            'parent_id' => null, // Will be set by seeder
        ];
    }

    /**
     * Indicate that this location is a mountain (level 0).
     */
    public function mountain(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 0,
            'gps_lat' => fake()->latitude(25, 50),
            'gps_lng' => fake()->longitude(-125, -70),
            'parent_id' => null,
        ]);
    }

    /**
     * Indicate that this location is a cliff (level 1).
     */
    public function cliff(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 1,
            'gps_lat' => null,
            'gps_lng' => null,
        ]);
    }

    /**
     * Indicate that this location is a sector (level 2).
     */
    public function sector(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
            'gps_lat' => null,
            'gps_lng' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Realistic climbing route name prefixes
        $prefixes = [
            'Crimson', 'Golden', 'Silver', 'Diamond', 'Crystal', 'Thunder', 'Lightning',
            'Shadow', 'Sunset', 'Dawn', 'Midnight', 'Solar', 'Lunar', 'Stellar',
            'Iron', 'Steel', 'Bronze', 'Copper', 'Emerald', 'Ruby', 'Sapphire'
        ];

        // Route name suffixes
        $suffixes = [
            'Dreams', 'Reality', 'Journey', 'Adventure', 'Challenge', 'Quest',
            'Arete', 'Wall', 'Face', 'Pillar', 'Corner', 'Crack', 'Chimney',
            'Roof', 'Overhang', 'Slab', 'Dihedral', 'Ridge', 'Buttress',
            'Direct', 'Route', 'Line', 'Climb'
        ];

        $gradeType = fake()->randomElement(['UIAA', 'French']);

        $uiaaGrades = ['3', '4-', '4', '4+', '5-', '5', '5+', '6-', '6', '6+', '7-', '7', '7+', '8-', '8', '8+', '9-', '9', '9+', '10-', '10', '10+', '11-', '11'];
        $frenchGrades = ['3a', '3b', '3c', '4a', '4b', '4c', '5a', '5b', '5c', '6a', '6a+', '6b', '6b+', '6c', '6c+', '7a', '7a+', '7b', '7b+', '7c', '7c+', '8a', '8a+', '8b', '8b+', '8c', '8c+', '9a', '9a+'];

        $gradeValue = $gradeType === 'UIAA'
            ? fake()->randomElement($uiaaGrades)
            : fake()->randomElement($frenchGrades);

        $routeType = fake()->randomElement(['Alpine', 'Sport', 'Traditional']);
        $status = fake()->randomElement(['New', 'Equipped', 'Needs Repair', 'Closed']);
        $riskRating = fake()->randomElement(['None', 'R', 'X']);

        // Approach descriptions
        $approaches = [
            '5-minute walk from parking on well-marked trail',
            '15-minute uphill hike with 100m elevation gain',
            'Short 3-minute approach on flat ground',
            '20-minute moderate hike through forest',
            '30-minute approach with steep sections',
            '10-minute walk along base of cliff',
            'Drive-up access, routes visible from parking',
            '25-minute approach with some scrambling required',
        ];

        // Descent descriptions
        $descents = [
            'Rappel from bolted anchors with two 60m ropes',
            'Walk off to climber\'s left, 10-minute descent trail',
            'Lower from chain anchors with single rope',
            'Rappel the route with one 70m rope',
            'Scramble down gully to climber\'s right',
            'Multi-pitch rappel from bolted stations',
            'Walk off to the north following cairns',
            'Lower or rappel from fixed anchors',
        ];

        // Gear descriptions by route type
        $sportGear = [
            '12-14 quickdraws, 60m rope',
            '10 quickdraws for protection',
            '8-10 quickdraws, 70m rope recommended',
            '15 quickdraws for this long pitch',
            'Standard sport rack: 12 draws',
        ];

        $tradGear = [
            'Standard trad rack: cams 0.3-3, set of nuts',
            'Double rack of cams from 0.5-4, full set of nuts',
            'Small to medium cams (0.3-2), nuts, tricams',
            'Large cam rack including #4 and #5 for wide section',
            'Cams to #3, doubles of 0.75-2, nuts',
            'Mixed rack: small cams, nuts, and some quickdraws',
        ];

        $alpineGear = [
            'Light alpine rack, 60m rope, helmet essential',
            'Standard rack, extra slings for long pitches',
            'Ice axe, crampons for approach, rock gear for climb',
            'Light rack, approach shoes, helmet',
            'Minimal rack: cams to #2, few slings, 50m rope',
        ];

        $gearByType = [
            'Sport' => $sportGear,
            'Traditional' => $tradGear,
            'Alpine' => $alpineGear,
        ];

        $isApproved = fake()->boolean(75); // 75% approved, 25% pending

        return [
            'name' => fake()->randomElement($prefixes) . ' ' . fake()->randomElement($suffixes),
            'location_id' => Location::inRandomOrder()->first()?->id ?? Location::factory(),
            'created_by_user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'length_m' => fake()->numberBetween(8, 400),
            'pitch_count' => fake()->numberBetween(1, 15),
            'grade_type' => $gradeType,
            'grade_value' => $gradeValue,
            'risk_rating' => $riskRating,
            'approach_description' => fake()->randomElement($approaches),
            'descent_description' => fake()->randomElement($descents),
            'required_gear' => fake()->randomElement($gearByType[$routeType]),
            'route_type' => $routeType,
            'status' => $status,
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'approved_by_user_id' => $isApproved ? User::inRandomOrder()->first()?->id : null,
        ];
    }

    /**
     * Indicate that the route is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'approved_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'approved_by_user_id' => User::inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the route is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the route is a sport route.
     */
    public function sport(): static
    {
        return $this->state(fn (array $attributes) => [
            'route_type' => 'Sport',
            'required_gear' => fake()->randomElement([
                '12-14 quickdraws, 60m rope',
                '10 quickdraws for protection',
                '8-10 quickdraws, 70m rope recommended',
            ]),
        ]);
    }

    /**
     * Indicate that the route is a traditional route.
     */
    public function traditional(): static
    {
        return $this->state(fn (array $attributes) => [
            'route_type' => 'Traditional',
            'required_gear' => fake()->randomElement([
                'Standard trad rack: cams 0.3-3, set of nuts',
                'Double rack of cams from 0.5-4, full set of nuts',
                'Small to medium cams (0.3-2), nuts, tricams',
            ]),
        ]);
    }

    /**
     * Indicate that the route is an alpine route.
     */
    public function alpine(): static
    {
        return $this->state(fn (array $attributes) => [
            'route_type' => 'Alpine',
            'required_gear' => fake()->randomElement([
                'Light alpine rack, 60m rope, helmet essential',
                'Standard rack, extra slings for long pitches',
                'Minimal rack: cams to #2, few slings, 50m rope',
            ]),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Space::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $location = $this->faker->city;
        $visibilities = ['public', 'friends-only', 'admin-only', 'secret'];
        $vis = $visibilities[rand(0, 3)];
        $mechanisms = ['anyone_join', 'anyone_request', 'member_invite', 'admin_invite'];
        $mechanism = $mechanisms[rand(0, 3)];

        return [
            'name' => $location." space",
            'location' => $location,
            'space_type' => 'space',
            'visibility' => $vis,
            'invite_mechanism' => $mechanism,
            'creator_id' => User::factory()->create()->id
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Block;
use App\Models\User;
use App\Models\Space;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Define the model's default state.
     * creates a channel. relates block to the space that the channel
     * is related to. A new user is also created to own the block.
     * @return array
     */
    public function definition()
    {
        $channel =  Channel::factory()->create();
        $space = Space::find($channel->space_id);

        $personal_space = Space::find($space->id - 1);
        $name = User::find($personal_space->creator_id)->name;
        $personal_space->name = $name."'s personal space";
        $personal_space->save();

        $personal_space->channels()->createMany([
            [
                'name' => "homepage",
                'type' => 'list',
                'visibility' => 'public',
                'display_method' => 'list',
                'can_send_message' => false
            ],
            [
                'name' => "circle",
                'type' => 'list',
                'visibility' => 'friends-only',
                'display_method' => 'list',
                'can_send_message' => false,
            ],
            [
                'name' => "favorites",
                'type' => 'list',
                'visibility' => 'secret',
                'display_method' => 'list',
                'can_send_message' => false,
            ]
        ]);


        return [
            'title' => $this->faker->jobTitle,
            'location' => $this->faker->city,
            'user_id' => 1,
            'space_id' => $space->id,
            'Channel_id' => $channel->id,
            'message_block'=> false,
            'created_at' => now()
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Channel::class;

    /**
     * Define the model's default state.
     * creates a user w/ personal space + another space
     * created by that user that the channel belongs to
     * @return array
     */
    public function definition()
    {
        $personal_space = Space::factory()->create([
            'invite_mechanism' => 'none',
            'space_type' => 'personal',
            'visibility' => 'public'
        ]);
        $user = User::find($personal_space->creator_id);
        $user->personal_space_id = $personal_space->id;
        $user->save();
        $id_to_delete = $user->id + 1;
        $personal_space->members()->attach($user->id, [
            'status' => 'yes', 'inviter_id' => $user->id
        ]);

        $new_space = Space::factory()->create([
            'creator_id' => 1
        ]);
        $new_space->save();

        if ($new_space->type != "chat"){
            $new_space->channels()->create([
                'name' => "homepage",
                'type' => 'list',
                'visibility' => $new_space->visibility,
                'display_method' => 'space_homepage',
                'can_send_message' => false
            ]);
        }

        $new_space->channels()->createMany([
            [
                'name' => "feed",
                'type' => 'list',
                'visibility' => $new_space->visibility,
                'display_method' => 'feed',
                'can_send_message' => false,
            ],
            [
                'name' => "events",
                'type' => 'list',
                'visibility' => $new_space->visibility,
                'display_method' => 'list',
                'can_send_message' => false,
            ]
        ]);

        User::find($id_to_delete)->delete();

        foreach(User::all() as $u){
            $new_space->members()->attach($u->id, [
                'status' => 'yes', 'inviter_id' => 1
            ]);
        }

        return [
            'name' => $this->faker->name." channel",
            'type' => 'list',
            'display_method' => 'feed',
            'visibility'=> 'public',
            'can_send_message' => True,
            'space_id' => $new_space->id
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name,
            'phone_number' => $this->faker->unique()->numberBetween(10000000000,99999999999),
            'phone_number_verified_at' => now(),
            'username' => $this->faker->userName,
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10)
        ];
    }
}

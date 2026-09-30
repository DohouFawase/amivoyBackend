<?php

namespace Database\Factories;

use App\Models\Circle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Circle> */
class CircleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'name' => fake()->words(2, true),
            'members' => ['Samira', 'Amadou', 'Mariam'],
            'member_user_ids' => [],
        ];
    }
}

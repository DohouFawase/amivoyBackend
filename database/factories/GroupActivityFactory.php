<?php

namespace Database\Factories;

use App\Models\GroupActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GroupActivity> */
class GroupActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => 'outing',
            'group_id' => fake()->uuid(),
            'group_name' => 'Sortie entre amis',
            'title' => 'Une sortie a été organisée',
            'description' => 'Place de l’Étoile · Ce soir à 19:00',
            'actor' => 'Samira',
            'time_label' => 'Aujourd’hui · 18:00',
            'icon' => 'outing',
            'href' => '/outings',
        ];
    }
}

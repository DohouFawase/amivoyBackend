<?php

namespace Database\Factories;

use App\Models\Outing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Outing> */
class OutingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'title' => fake()->sentence(3),
            'place' => 'Place de l’Étoile, Cotonou',
            'location_type' => 'public',
            'category' => 'Sortie entre amis',
            'date_label' => 'Ce soir',
            'time_label' => '19:00',
            'note' => fake()->sentence(),
            'activity' => 'Dîner ensemble',
            'budget_target' => 50000,
            'currency' => 'XOF',
            'contributions' => [],
            'latitude' => 6.3702,
            'longitude' => 2.4251,
            'guests' => ['Samira', 'Amadou'],
            'participant_user_ids' => [],
            'attending' => ['Samira'],
            'checked_in' => [],
            'started' => false,
            'ended' => false,
        ];
    }
}

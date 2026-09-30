<?php

namespace Database\Seeders;

use App\Models\Circle;
use App\Models\Outing;
use Illuminate\Database\Seeder;

class OutingSeeder extends Seeder
{
    public function run(): void
    {
        Outing::factory()->count(3)->create([
            'circle_id' => Circle::factory(),
        ]);
    }
}

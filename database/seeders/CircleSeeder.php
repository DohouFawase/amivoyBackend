<?php

namespace Database\Seeders;

use App\Models\Circle;
use Illuminate\Database\Seeder;

class CircleSeeder extends Seeder
{
    public function run(): void
    {
        Circle::factory()->count(3)->create();
    }
}

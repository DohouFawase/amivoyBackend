<?php

namespace Database\Seeders;

use App\Models\GroupActivity;
use Illuminate\Database\Seeder;

class GroupActivitySeeder extends Seeder
{
    public function run(): void
    {
        GroupActivity::factory()->count(8)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Block::factory()
            ->count(3)
            ->create();
    }
}

<?php

namespace ThunderPack\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Thunder-Pack package data.
     */
    public function run(): void
    {
        $this->call([
            AvailableLimitsSeeder::class,
        ]);
    }
}
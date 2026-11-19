<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // If you have a complete Indonesia dataset, run IndonesiaRegionsSeeder
            IndonesiaRegionsSeeder::class,
            // Fallback small seeders (will still run; IndonesiaRegionsSeeder will skip if files missing)
            CitySeeder::class,
            AllIndonesianCitiesSeeder::class,
            // DistrictSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            // HelpSeeder::class, // Dikosongkan untuk deployment bersih (tanpa sample order/permintaan bantuan)
            \Database\Seeders\UserBalancesSeeder::class,
        ]);
    }
}

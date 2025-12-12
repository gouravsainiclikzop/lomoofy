<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InitialDataSeeder::class,
            EnhancedRolesAndPermissionsSeeder::class,
            // Reset all product-related data first
            ResetProductDataSeeder::class,
            // Seed attributes and values
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            // Seed products (requires attributes to be seeded first)
            ProductSeeder::class,
        ]);
    }
}

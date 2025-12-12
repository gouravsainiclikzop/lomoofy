<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'New',
                'slug' => 'new',
                'description' => 'Newly created lead',
                'color' => 'bg-info',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in_progress',
                'description' => 'Lead is being actively worked on',
                'color' => 'bg-primary',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Qualified',
                'slug' => 'qualified',
                'description' => 'Lead has been qualified and is ready for conversion',
                'color' => 'bg-success',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Lost',
                'slug' => 'lost',
                'description' => 'Lead opportunity was lost',
                'color' => 'bg-danger',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Won',
                'slug' => 'won',
                'description' => 'Lead was successfully converted',
                'color' => 'bg-success',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('lead_statuses')->insert(array_merge($status, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Low',
                'slug' => 'low',
                'description' => 'Low priority lead',
                'color' => 'bg-success',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Medium',
                'slug' => 'medium',
                'description' => 'Medium priority lead',
                'color' => 'bg-warning',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'High',
                'slug' => 'high',
                'description' => 'High priority lead',
                'color' => 'bg-danger',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($priorities as $priority) {
            DB::table('lead_priorities')->insert(array_merge($priority, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Website',
                'slug' => 'website',
                'description' => 'Lead came from website',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Referral',
                'slug' => 'referral',
                'description' => 'Lead came from referral',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Ads',
                'slug' => 'ads',
                'description' => 'Lead came from advertising campaigns',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Social',
                'slug' => 'social',
                'description' => 'Lead came from social media',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Offline',
                'slug' => 'offline',
                'description' => 'Lead came from offline channels',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($sources as $source) {
            DB::table('lead_sources')->insert(array_merge($source, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

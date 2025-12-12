<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // Grocery Brands
            [
                'name' => 'Tesco',
                'slug' => 'tesco',
                'description' => 'Leading UK supermarket chain offering a wide range of grocery products.',
                'website' => 'https://www.tesco.com',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sainsbury\'s',
                'slug' => 'sainsburys',
                'description' => 'British supermarket chain with quality food and household products.',
                'website' => 'https://www.sainsburys.co.uk',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Asda',
                'slug' => 'asda',
                'description' => 'Value-focused supermarket chain offering affordable groceries.',
                'website' => 'https://www.asda.com',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Morrisons',
                'slug' => 'morrisons',
                'description' => 'British supermarket chain known for fresh food and quality products.',
                'website' => 'https://www.morrisons.com',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Waitrose',
                'slug' => 'waitrose',
                'description' => 'Premium supermarket chain offering high-quality food products.',
                'website' => 'https://www.waitrose.com',
                'is_active' => true,
                'sort_order' => 5,
            ],

            // Electronics Brands
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Innovative technology company known for premium electronics and devices.',
                'website' => 'https://www.apple.com',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Global technology leader in electronics, mobile devices, and appliances.',
                'website' => 'https://www.samsung.com',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Japanese multinational specializing in consumer and professional electronics.',
                'website' => 'https://www.sony.com',
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'South Korean multinational electronics company.',
                'website' => 'https://www.lg.com',
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'Microsoft',
                'slug' => 'microsoft',
                'description' => 'Technology corporation known for software, hardware, and cloud services.',
                'website' => 'https://www.microsoft.com',
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'Dell',
                'slug' => 'dell',
                'description' => 'American technology company specializing in computer hardware.',
                'website' => 'https://www.dell.com',
                'is_active' => true,
                'sort_order' => 15,
            ],

            // Fashion Brands
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Global athletic footwear and apparel company.',
                'website' => 'https://www.nike.com',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'German multinational corporation focused on sports shoes and clothing.',
                'website' => 'https://www.adidas.com',
                'is_active' => true,
                'sort_order' => 21,
            ],
            [
                'name' => 'Zara',
                'slug' => 'zara',
                'description' => 'Spanish fast fashion retailer offering trendy clothing.',
                'website' => 'https://www.zara.com',
                'is_active' => true,
                'sort_order' => 22,
            ],
            [
                'name' => 'H&M',
                'slug' => 'hm',
                'description' => 'Swedish multinational clothing retailer.',
                'website' => 'https://www.hm.com',
                'is_active' => true,
                'sort_order' => 23,
            ],
            [
                'name' => 'Uniqlo',
                'slug' => 'uniqlo',
                'description' => 'Japanese casual wear designer, manufacturer and retailer.',
                'website' => 'https://www.uniqlo.com',
                'is_active' => true,
                'sort_order' => 24,
            ],
            [
                'name' => 'Levi\'s',
                'slug' => 'levis',
                'description' => 'American clothing company known for denim jeans.',
                'website' => 'https://www.levi.com',
                'is_active' => true,
                'sort_order' => 25,
            ],

            // Digital/Software Brands
            [
                'name' => 'Adobe',
                'slug' => 'adobe',
                'description' => 'Multinational computer software company known for creative software.',
                'website' => 'https://www.adobe.com',
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'Autodesk',
                'slug' => 'autodesk',
                'description' => 'American multinational software corporation for 3D design.',
                'website' => 'https://www.autodesk.com',
                'is_active' => true,
                'sort_order' => 31,
            ],
            [
                'name' => 'Salesforce',
                'slug' => 'salesforce',
                'description' => 'Cloud-based software company providing customer relationship management.',
                'website' => 'https://www.salesforce.com',
                'is_active' => true,
                'sort_order' => 32,
            ],
            [
                'name' => 'Oracle',
                'slug' => 'oracle',
                'description' => 'American multinational computer technology corporation.',
                'website' => 'https://www.oracle.com',
                'is_active' => true,
                'sort_order' => 33,
            ],

            // Service Brands
            [
                'name' => 'Amazon Web Services',
                'slug' => 'amazon-web-services',
                'description' => 'Comprehensive cloud computing platform by Amazon.',
                'website' => 'https://aws.amazon.com',
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'Google Cloud',
                'slug' => 'google-cloud',
                'description' => 'Cloud computing services by Google.',
                'website' => 'https://cloud.google.com',
                'is_active' => true,
                'sort_order' => 41,
            ],
            [
                'name' => 'Uber',
                'slug' => 'uber',
                'description' => 'American multinational ride-hailing company.',
                'website' => 'https://www.uber.com',
                'is_active' => true,
                'sort_order' => 42,
            ],
            [
                'name' => 'Airbnb',
                'slug' => 'airbnb',
                'description' => 'Online marketplace for short-term homestays and experiences.',
                'website' => 'https://www.airbnb.com',
                'is_active' => true,
                'sort_order' => 43,
            ],
            [
                'name' => 'Netflix',
                'slug' => 'netflix',
                'description' => 'Global streaming entertainment service.',
                'website' => 'https://www.netflix.com',
                'is_active' => true,
                'sort_order' => 44,
            ],

            // Home & Garden Brands
            [
                'name' => 'IKEA',
                'slug' => 'ikea',
                'description' => 'Swedish multinational group that designs and sells furniture.',
                'website' => 'https://www.ikea.com',
                'is_active' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'Home Depot',
                'slug' => 'home-depot',
                'description' => 'American multinational home improvement retail corporation.',
                'website' => 'https://www.homedepot.com',
                'is_active' => true,
                'sort_order' => 51,
            ],
            [
                'name' => 'John Lewis',
                'slug' => 'john-lewis',
                'description' => 'British department store chain selling homeware and clothing.',
                'website' => 'https://www.johnlewis.com',
                'is_active' => true,
                'sort_order' => 52,
            ],

            // Automotive Brands
            [
                'name' => 'Tesla',
                'slug' => 'tesla',
                'description' => 'American electric vehicle and clean energy company.',
                'website' => 'https://www.tesla.com',
                'is_active' => true,
                'sort_order' => 60,
            ],
            [
                'name' => 'BMW',
                'slug' => 'bmw',
                'description' => 'German multinational corporation which produces luxury vehicles.',
                'website' => 'https://www.bmw.com',
                'is_active' => true,
                'sort_order' => 61,
            ],
            [
                'name' => 'Mercedes-Benz',
                'slug' => 'mercedes-benz',
                'description' => 'German luxury automotive brand.',
                'website' => 'https://www.mercedes-benz.com',
                'is_active' => true,
                'sort_order' => 62,
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::create($brandData);
        }
    }
}

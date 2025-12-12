<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // GROCERY CATEGORIES
            [
                'name' => 'Fresh Produce',
                'slug' => 'fresh-produce',
                'description' => 'Fresh fruits, vegetables, and organic produce',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fruits',
                'slug' => 'fruits',
                'description' => 'Fresh and seasonal fruits',
                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Vegetables',
                'slug' => 'vegetables',
                'description' => 'Fresh and organic vegetables',
                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Dairy & Eggs',
                'slug' => 'dairy-eggs',
                'description' => 'Milk, cheese, yogurt, and fresh eggs',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Meat & Seafood',
                'slug' => 'meat-seafood',
                'description' => 'Fresh meat, poultry, and seafood products',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Bakery',
                'slug' => 'bakery',
                'description' => 'Fresh bread, pastries, and baked goods',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pantry Staples',
                'slug' => 'pantry-staples',
                'description' => 'Rice, pasta, canned goods, and cooking essentials',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Drinks, juices, and liquid refreshments',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Frozen Foods',
                'slug' => 'frozen-foods',
                'description' => 'Frozen meals, vegetables, and desserts',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Snacks & Confectionery',
                'slug' => 'snacks-confectionery',
                'description' => 'Chips, chocolates, and sweet treats',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Vitamins, supplements, and health products',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 9,
            ],

            // ELECTRONICS CATEGORIES
            [
                'name' => 'Mobile Devices',
                'slug' => 'mobile-devices',
                'description' => 'Smartphones, tablets, and mobile accessories',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'description' => 'Latest smartphones and mobile devices',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 101,
            ],
            [
                'name' => 'Tablets',
                'slug' => 'tablets',
                'description' => 'iPad, Android tablets, and accessories',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 102,
            ],
            [
                'name' => 'Computers & Laptops',
                'slug' => 'computers-laptops',
                'description' => 'Desktop computers, laptops, and accessories',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'description' => 'Portable computers and laptops',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 111,
            ],
            [
                'name' => 'Desktop Computers',
                'slug' => 'desktop-computers',
                'description' => 'Desktop PCs, workstations, and components',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 112,
            ],
            [
                'name' => 'Audio & Headphones',
                'slug' => 'audio-headphones',
                'description' => 'Speakers, headphones, and audio equipment',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'TVs & Displays',
                'slug' => 'tvs-displays',
                'description' => 'Televisions, monitors, and display screens',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'description' => 'Gaming consoles, accessories, and games',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'Home Appliances',
                'slug' => 'home-appliances',
                'description' => 'Kitchen and home appliances',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'name' => 'Cameras & Photography',
                'slug' => 'cameras-photography',
                'description' => 'Digital cameras, lenses, and photography equipment',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'name' => 'Wearables',
                'slug' => 'wearables',
                'description' => 'Smartwatches, fitness trackers, and wearable tech',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 17,
            ],

            // FASHION CATEGORIES
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Men\'s, women\'s, and children\'s clothing',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'description' => 'Men\'s fashion and apparel',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 201,
            ],
            [
                'name' => 'Women\'s Clothing',
                'slug' => 'womens-clothing',
                'description' => 'Women\'s fashion and apparel',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 202,
            ],
            [
                'name' => 'Children\'s Clothing',
                'slug' => 'childrens-clothing',
                'description' => 'Kids and baby clothing',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 203,
            ],
            [
                'name' => 'Shoes',
                'slug' => 'shoes',
                'description' => 'Footwear for men, women, and children',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 21,
            ],
            [
                'name' => 'Men\'s Shoes',
                'slug' => 'mens-shoes',
                'description' => 'Men\'s footwear and sneakers',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 211,
            ],
            [
                'name' => 'Women\'s Shoes',
                'slug' => 'womens-shoes',
                'description' => 'Women\'s footwear, heels, and flats',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 212,
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Bags, watches, jewelry, and fashion accessories',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 22,
            ],
            [
                'name' => 'Bags & Handbags',
                'slug' => 'bags-handbags',
                'description' => 'Handbags, backpacks, and luggage',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 221,
            ],
            [
                'name' => 'Jewelry & Watches',
                'slug' => 'jewelry-watches',
                'description' => 'Rings, necklaces, watches, and jewelry',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 222,
            ],
            [
                'name' => 'Sportswear',
                'slug' => 'sportswear',
                'description' => 'Athletic and sports clothing',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 23,
            ],
            [
                'name' => 'Denim',
                'slug' => 'denim',
                'description' => 'Jeans and denim clothing',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 24,
            ],
            [
                'name' => 'Underwear & Lingerie',
                'slug' => 'underwear-lingerie',
                'description' => 'Underwear, bras, and intimate apparel',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 25,
            ],
            [
                'name' => 'Outerwear',
                'slug' => 'outerwear',
                'description' => 'Jackets, coats, and outerwear',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 26,
            ],

            // DIGITAL/SOFTWARE CATEGORIES
            [
                'name' => 'Creative Software',
                'slug' => 'creative-software',
                'description' => 'Design, photo editing, and creative software',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'Design Tools',
                'slug' => 'design-tools',
                'description' => '3D modeling, CAD, and design software',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 31,
            ],
            [
                'name' => 'Business Software',
                'slug' => 'business-software',
                'description' => 'CRM, productivity, and business management tools',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 32,
            ],
            [
                'name' => 'Database Software',
                'slug' => 'database-software',
                'description' => 'Database management and enterprise software',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 33,
            ],
            [
                'name' => 'Mobile Apps',
                'slug' => 'mobile-apps',
                'description' => 'iOS and Android applications',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 34,
            ],

            // SERVICE CATEGORIES
            [
                'name' => 'Cloud Computing',
                'slug' => 'cloud-computing',
                'description' => 'Cloud infrastructure and computing services',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'Transportation',
                'slug' => 'transportation',
                'description' => 'Ride-sharing and transportation services',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 41,
            ],
            [
                'name' => 'Accommodation',
                'slug' => 'accommodation',
                'description' => 'Short-term rentals and accommodation services',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 42,
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'description' => 'Streaming and digital entertainment services',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 43,
            ],
            [
                'name' => 'Consulting',
                'slug' => 'consulting',
                'description' => 'Professional consulting and advisory services',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 44,
            ],

            // HOME & GARDEN CATEGORIES
            [
                'name' => 'Furniture',
                'slug' => 'furniture',
                'description' => 'Home and office furniture',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'Living Room Furniture',
                'slug' => 'living-room-furniture',
                'description' => 'Sofas, chairs, coffee tables, and living room sets',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 501,
            ],
            [
                'name' => 'Bedroom Furniture',
                'slug' => 'bedroom-furniture',
                'description' => 'Beds, dressers, nightstands, and bedroom sets',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 502,
            ],
            [
                'name' => 'Kitchen & Dining',
                'slug' => 'kitchen-dining',
                'description' => 'Kitchen furniture, dining tables, and chairs',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 503,
            ],
            [
                'name' => 'Home Improvement',
                'slug' => 'home-improvement',
                'description' => 'DIY tools and home improvement products',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 51,
            ],
            [
                'name' => 'Power Tools',
                'slug' => 'power-tools',
                'description' => 'Drills, saws, and power equipment',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 511,
            ],
            [
                'name' => 'Hand Tools',
                'slug' => 'hand-tools',
                'description' => 'Screwdrivers, hammers, and manual tools',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 512,
            ],
            [
                'name' => 'Home Decor',
                'slug' => 'home-decor',
                'description' => 'Decorative items and home accessories',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 52,
            ],
            [
                'name' => 'Wall Art & Mirrors',
                'slug' => 'wall-art-mirrors',
                'description' => 'Paintings, prints, mirrors, and wall decorations',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 521,
            ],
            [
                'name' => 'Lighting',
                'slug' => 'lighting',
                'description' => 'Lamps, chandeliers, and lighting fixtures',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 522,
            ],
            [
                'name' => 'Garden & Outdoor',
                'slug' => 'garden-outdoor',
                'description' => 'Garden tools, plants, and outdoor equipment',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 53,
            ],
            [
                'name' => 'Garden Tools',
                'slug' => 'garden-tools',
                'description' => 'Shovels, rakes, and gardening equipment',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 531,
            ],
            [
                'name' => 'Outdoor Furniture',
                'slug' => 'outdoor-furniture',
                'description' => 'Patio sets, outdoor chairs, and garden furniture',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 532,
            ],

            // AUTOMOTIVE CATEGORIES
            [
                'name' => 'Electric Vehicles',
                'slug' => 'electric-vehicles',
                'description' => 'Electric cars and sustainable transportation',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 60,
            ],
            [
                'name' => 'Luxury Cars',
                'slug' => 'luxury-cars',
                'description' => 'Premium and luxury vehicles',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 61,
            ],
            [
                'name' => 'Auto Parts',
                'slug' => 'auto-parts',
                'description' => 'Car parts, accessories, and maintenance',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 62,
            ],

            // BEAUTY & PERSONAL CARE CATEGORIES
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'description' => 'Cosmetics, skincare, and personal care products',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 70,
            ],
            [
                'name' => 'Skincare',
                'slug' => 'skincare',
                'description' => 'Face creams, cleansers, and skincare products',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 701,
            ],
            [
                'name' => 'Makeup',
                'slug' => 'makeup',
                'description' => 'Foundation, lipstick, and cosmetic products',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 702,
            ],
            [
                'name' => 'Hair Care',
                'slug' => 'hair-care',
                'description' => 'Shampoo, conditioner, and hair styling products',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 703,
            ],

            // SPORTS & FITNESS CATEGORIES
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Sports equipment, fitness gear, and athletic accessories',                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 80,
            ],
            [
                'name' => 'Fitness Equipment',
                'slug' => 'fitness-equipment',
                'description' => 'Gym equipment, weights, and fitness machines',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 801,
            ],
            [
                'name' => 'Team Sports',
                'slug' => 'team-sports',
                'description' => 'Football, basketball, soccer equipment',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 802,
            ],
            [
                'name' => 'Outdoor Sports',
                'slug' => 'outdoor-sports',
                'description' => 'Hiking, camping, and outdoor adventure gear',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 803,
            ],

            // BOOKS & MEDIA CATEGORIES
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'description' => 'Books, magazines, and digital media',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 90,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Fiction, non-fiction, and educational books',
                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 901,
            ],
            [
                'name' => 'Magazines',
                'slug' => 'magazines',
                'description' => 'Lifestyle, fashion, and news magazines',
                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 902,
            ],
            [
                'name' => 'Digital Media',
                'slug' => 'digital-media',
                'description' => 'E-books, digital magazines, and online content',                'parent_id' => null, // Will be set after parent creation
                'is_active' => true,
                'sort_order' => 903,
            ],
        ];

        // Create categories and store IDs for parent-child relationships
        $createdCategories = [];
        
        foreach ($categories as $categoryData) {
            // Remove brand_id if it exists (no longer used)
            unset($categoryData['brand_id']);
            
            $category = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
            $createdCategories[$category->slug] = $category;
        }

        // Update parent-child relationships
        // Grocery categories
        if (isset($createdCategories['fruits'])) {
            $createdCategories['fruits']->update(['parent_id' => $createdCategories['fresh-produce']->id]);
        }
        if (isset($createdCategories['vegetables'])) {
            $createdCategories['vegetables']->update(['parent_id' => $createdCategories['fresh-produce']->id]);
        }
        
        // Electronics categories
        if (isset($createdCategories['smartphones'])) {
            $createdCategories['smartphones']->update(['parent_id' => $createdCategories['mobile-devices']->id]);
        }
        if (isset($createdCategories['tablets'])) {
            $createdCategories['tablets']->update(['parent_id' => $createdCategories['mobile-devices']->id]);
        }
        if (isset($createdCategories['laptops'])) {
            $createdCategories['laptops']->update(['parent_id' => $createdCategories['computers-laptops']->id]);
        }
        if (isset($createdCategories['desktop-computers'])) {
            $createdCategories['desktop-computers']->update(['parent_id' => $createdCategories['computers-laptops']->id]);
        }
        
        // Fashion categories
        if (isset($createdCategories['mens-clothing'])) {
            $createdCategories['mens-clothing']->update(['parent_id' => $createdCategories['clothing']->id]);
        }
        if (isset($createdCategories['womens-clothing'])) {
            $createdCategories['womens-clothing']->update(['parent_id' => $createdCategories['clothing']->id]);
        }
        if (isset($createdCategories['childrens-clothing'])) {
            $createdCategories['childrens-clothing']->update(['parent_id' => $createdCategories['clothing']->id]);
        }
        if (isset($createdCategories['mens-shoes'])) {
            $createdCategories['mens-shoes']->update(['parent_id' => $createdCategories['shoes']->id]);
        }
        if (isset($createdCategories['womens-shoes'])) {
            $createdCategories['womens-shoes']->update(['parent_id' => $createdCategories['shoes']->id]);
        }
        if (isset($createdCategories['bags-handbags'])) {
            $createdCategories['bags-handbags']->update(['parent_id' => $createdCategories['accessories']->id]);
        }
        if (isset($createdCategories['jewelry-watches'])) {
            $createdCategories['jewelry-watches']->update(['parent_id' => $createdCategories['accessories']->id]);
        }
        
        // Home & Garden categories
        if (isset($createdCategories['living-room-furniture'])) {
            $createdCategories['living-room-furniture']->update(['parent_id' => $createdCategories['furniture']->id]);
        }
        if (isset($createdCategories['bedroom-furniture'])) {
            $createdCategories['bedroom-furniture']->update(['parent_id' => $createdCategories['furniture']->id]);
        }
        if (isset($createdCategories['kitchen-dining'])) {
            $createdCategories['kitchen-dining']->update(['parent_id' => $createdCategories['furniture']->id]);
        }
        if (isset($createdCategories['power-tools'])) {
            $createdCategories['power-tools']->update(['parent_id' => $createdCategories['home-improvement']->id]);
        }
        if (isset($createdCategories['hand-tools'])) {
            $createdCategories['hand-tools']->update(['parent_id' => $createdCategories['home-improvement']->id]);
        }
        if (isset($createdCategories['wall-art-mirrors'])) {
            $createdCategories['wall-art-mirrors']->update(['parent_id' => $createdCategories['home-decor']->id]);
        }
        if (isset($createdCategories['lighting'])) {
            $createdCategories['lighting']->update(['parent_id' => $createdCategories['home-decor']->id]);
        }
        if (isset($createdCategories['garden-tools'])) {
            $createdCategories['garden-tools']->update(['parent_id' => $createdCategories['garden-outdoor']->id]);
        }
        if (isset($createdCategories['outdoor-furniture'])) {
            $createdCategories['outdoor-furniture']->update(['parent_id' => $createdCategories['garden-outdoor']->id]);
        }
        
        // Beauty & Personal Care categories
        if (isset($createdCategories['skincare'])) {
            $createdCategories['skincare']->update(['parent_id' => $createdCategories['beauty-personal-care']->id]);
        }
        if (isset($createdCategories['makeup'])) {
            $createdCategories['makeup']->update(['parent_id' => $createdCategories['beauty-personal-care']->id]);
        }
        if (isset($createdCategories['hair-care'])) {
            $createdCategories['hair-care']->update(['parent_id' => $createdCategories['beauty-personal-care']->id]);
        }
        
        // Sports & Fitness categories
        if (isset($createdCategories['fitness-equipment'])) {
            $createdCategories['fitness-equipment']->update(['parent_id' => $createdCategories['sports-fitness']->id]);
        }
        if (isset($createdCategories['team-sports'])) {
            $createdCategories['team-sports']->update(['parent_id' => $createdCategories['sports-fitness']->id]);
        }
        if (isset($createdCategories['outdoor-sports'])) {
            $createdCategories['outdoor-sports']->update(['parent_id' => $createdCategories['sports-fitness']->id]);
        }
        
        // Books & Media categories
        if (isset($createdCategories['books'])) {
            $createdCategories['books']->update(['parent_id' => $createdCategories['books-media']->id]);
        }
        if (isset($createdCategories['magazines'])) {
            $createdCategories['magazines']->update(['parent_id' => $createdCategories['books-media']->id]);
        }
        if (isset($createdCategories['digital-media'])) {
            $createdCategories['digital-media']->update(['parent_id' => $createdCategories['books-media']->id]);
        }
    }
}

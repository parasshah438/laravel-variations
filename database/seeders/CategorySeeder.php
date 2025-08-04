<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Main Categories (Parents)
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'icon' => 'bi-bag',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Men\'s Clothing', 'slug' => 'mens-clothing', 'icon' => 'bi-person', 'sort_order' => 1],
                    ['name' => 'Women\'s Clothing', 'slug' => 'womens-clothing', 'icon' => 'bi-person-dress', 'sort_order' => 2],
                    ['name' => 'Kids\' Clothing', 'slug' => 'kids-clothing', 'icon' => 'bi-person-hearts', 'sort_order' => 3],
                    ['name' => 'Shoes', 'slug' => 'shoes', 'icon' => 'bi-boot', 'sort_order' => 4],
                    ['name' => 'Accessories', 'slug' => 'accessories', 'icon' => 'bi-bag-heart', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => 'bi-laptop',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'smartphones', 'icon' => 'bi-phone', 'sort_order' => 1],
                    ['name' => 'Laptops', 'slug' => 'laptops', 'icon' => 'bi-laptop', 'sort_order' => 2],
                    ['name' => 'Tablets', 'slug' => 'tablets', 'icon' => 'bi-tablet', 'sort_order' => 3],
                    ['name' => 'Audio', 'slug' => 'audio', 'icon' => 'bi-headphones', 'sort_order' => 4],
                    ['name' => 'Cameras', 'slug' => 'cameras', 'icon' => 'bi-camera', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => 'bi-house',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Furniture', 'slug' => 'furniture', 'icon' => 'bi-door-open', 'sort_order' => 1],
                    ['name' => 'Kitchen', 'slug' => 'kitchen', 'icon' => 'bi-cup-hot', 'sort_order' => 2],
                    ['name' => 'Bedroom', 'slug' => 'bedroom', 'icon' => 'bi-moon', 'sort_order' => 3],
                    ['name' => 'Garden', 'slug' => 'garden', 'icon' => 'bi-flower1', 'sort_order' => 4],
                    ['name' => 'Decor', 'slug' => 'decor', 'icon' => 'bi-palette', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'icon' => 'bi-bicycle',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Fitness', 'slug' => 'fitness', 'icon' => 'bi-heart-pulse', 'sort_order' => 1],
                    ['name' => 'Outdoor Activities', 'slug' => 'outdoor-activities', 'icon' => 'bi-tree', 'sort_order' => 2],
                    ['name' => 'Sports Equipment', 'slug' => 'sports-equipment', 'icon' => 'bi-award', 'sort_order' => 3],
                    ['name' => 'Cycling', 'slug' => 'cycling', 'icon' => 'bi-bicycle', 'sort_order' => 4],
                ]
            ],
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'icon' => 'bi-heart',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Skincare', 'slug' => 'skincare', 'icon' => 'bi-droplet', 'sort_order' => 1],
                    ['name' => 'Makeup', 'slug' => 'makeup', 'icon' => 'bi-brush', 'sort_order' => 2],
                    ['name' => 'Hair Care', 'slug' => 'hair-care', 'icon' => 'bi-scissors', 'sort_order' => 3],
                    ['name' => 'Fragrances', 'slug' => 'fragrances', 'icon' => 'bi-flower2', 'sort_order' => 4],
                ]
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'icon' => 'bi-book',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Books', 'slug' => 'books', 'icon' => 'bi-book', 'sort_order' => 1],
                    ['name' => 'E-books', 'slug' => 'ebooks', 'icon' => 'bi-tablet', 'sort_order' => 2],
                    ['name' => 'Movies', 'slug' => 'movies', 'icon' => 'bi-film', 'sort_order' => 3],
                    ['name' => 'Music', 'slug' => 'music', 'icon' => 'bi-music-note', 'sort_order' => 4],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);
            
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'icon' => $categoryData['icon'],
                'sort_order' => $categoryData['sort_order'],
                'status' => true,
                'meta_title' => $categoryData['name'] . ' - Shop Online',
                'meta_description' => 'Discover amazing ' . strtolower($categoryData['name']) . ' products with great prices and quality.',
                'meta_keywords' => strtolower($categoryData['name']) . ', online shopping, products',
            ]);

            // Create child categories
            foreach ($children as $childData) {
                Category::create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'parent_id' => $parent->id,
                    'icon' => $childData['icon'],
                    'sort_order' => $childData['sort_order'],
                    'status' => true,
                    'meta_title' => $childData['name'] . ' - Shop Online',
                    'meta_description' => 'Shop for ' . strtolower($childData['name']) . ' with the best selection and prices.',
                    'meta_keywords' => strtolower($childData['name']) . ', ' . strtolower($parent->name) . ', online shopping',
                ]);
            }
        }
    }
}

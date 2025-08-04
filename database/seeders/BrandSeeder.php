<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Nike',
            'Adidas',
            'Apple',
            'Samsung',
            'Zara',
            'H&M',
            'Levi\'s',
            'Calvin Klein',
            'Tommy Hilfiger',
            'Polo Ralph Lauren',
            'Sony',
            'LG',
            'Dell',
            'HP',
            'Amazon Basics',
            'IKEA',
            'Philips',
            'Canon',
            'Nikon',
            'Uniqlo',
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name' => $brand,
                'slug' => Str::slug($brand),
            ]);
        }
    }
}

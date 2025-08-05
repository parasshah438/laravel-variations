<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the slider images directory exists
        if (!Storage::disk('public')->exists('sliders')) {
            Storage::disk('public')->makeDirectory('sliders');
        }

        // Sample slider data
        $sliders = [
            [
                'title' => 'Summer Sale - Up to 70% Off',
                'description' => 'Discover amazing deals on fashion, electronics, and home essentials. Limited time offer!',
                'image_path' => 'sliders/slider-1.jpg',
                'image_alt' => 'Summer Sale Banner',
                'button_text' => 'Shop Now',
                'button_link' => '/shop',
                'button_color' => '#ff6b6b',
                'text_position' => 'left',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 1,
                'start_date' => null,
                'end_date' => null
            ],
            [
                'title' => 'New Collection 2025',
                'description' => 'Explore our latest trendy collection with premium quality and modern designs.',
                'image_path' => 'sliders/slider-2.jpg',
                'image_alt' => 'New Collection 2025',
                'button_text' => 'Explore Collection',
                'button_link' => '/shop?sort=newest',
                'button_color' => '#4ecdc4',
                'text_position' => 'center',
                'text_color' => '#2c3e50',
                'is_active' => true,
                'sort_order' => 2,
                'start_date' => null,
                'end_date' => null
            ],
            [
                'title' => 'Free Shipping Worldwide',
                'description' => 'Get free shipping on all orders above $50. No minimum purchase required for premium members.',
                'image_path' => 'sliders/slider-3.jpg',
                'image_alt' => 'Free Shipping Offer',
                'button_text' => 'Learn More',
                'button_link' => '/shipping-info',
                'button_color' => '#45b7d1',
                'text_position' => 'right',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 3,
                'start_date' => null,
                'end_date' => null
            ],
            [
                'title' => 'Electronics Mega Sale',
                'description' => 'Upgrade your tech with our exclusive electronics sale. Smartphones, laptops, and more!',
                'image_path' => 'sliders/slider-4.jpg',
                'image_alt' => 'Electronics Sale',
                'button_text' => 'Shop Electronics',
                'button_link' => '/category/electronics',
                'button_color' => '#f39c12',
                'text_position' => 'left',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 4,
                'start_date' => null,
                'end_date' => null
            ],
            [
                'title' => 'Fashion Week Special',
                'description' => 'Be the trendsetter with our fashion week special collection. Premium brands at unbeatable prices.',
                'image_path' => 'sliders/slider-5.jpg',
                'image_alt' => 'Fashion Week Special',
                'button_text' => 'Shop Fashion',
                'button_link' => '/category/fashion',
                'button_color' => '#e74c3c',
                'text_position' => 'center',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 5,
                'start_date' => null,
                'end_date' => null
            ]
        ];

        foreach ($sliders as $sliderData) {
            Slider::create($sliderData);
        }

        $this->command->info('Sample sliders created successfully!');
        $this->command->info('Note: Please add actual slider images to storage/app/public/sliders/ directory.');
        $this->command->info('Recommended image dimensions: 1920x600 pixels for best results.');
    }
}

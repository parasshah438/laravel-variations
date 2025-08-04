<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder as BaseSeeder;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends BaseSeeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Size Attribute
        $sizeAttribute = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'sort_order' => 1,
            'is_active' => true
        ]);

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $index => $size) {
            AttributeValue::create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size,
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        // Create Color Attribute
        $colorAttribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
            'sort_order' => 2,
            'is_active' => true
        ]);

        $colors = [
            ['name' => 'Red', 'code' => '#FF0000'],
            ['name' => 'Blue', 'code' => '#0000FF'],
            ['name' => 'Green', 'code' => '#008000'],
            ['name' => 'Black', 'code' => '#000000'],
            ['name' => 'White', 'code' => '#FFFFFF'],
            ['name' => 'Yellow', 'code' => '#FFFF00'],
            ['name' => 'Orange', 'code' => '#FFA500'],
            ['name' => 'Purple', 'code' => '#800080'],
            ['name' => 'Pink', 'code' => '#FFC0CB'],
            ['name' => 'Brown', 'code' => '#A52A2A']
        ];

        foreach ($colors as $index => $color) {
            AttributeValue::create([
                'attribute_id' => $colorAttribute->id,
                'value' => $color['name'],
                'color_code' => $color['code'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        // Create Material Attribute
        $materialAttribute = Attribute::create([
            'name' => 'Material',
            'slug' => 'material',
            'type' => 'select',
            'sort_order' => 3,
            'is_active' => true
        ]);

        $materials = ['Cotton', 'Polyester', 'Silk', 'Wool', 'Linen', 'Denim', 'Leather'];
        foreach ($materials as $index => $material) {
            AttributeValue::create([
                'attribute_id' => $materialAttribute->id,
                'value' => $material,
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        // Create Style Attribute
        $styleAttribute = Attribute::create([
            'name' => 'Style',
            'slug' => 'style',
            'type' => 'select',
            'sort_order' => 4,
            'is_active' => true
        ]);

        $styles = ['Casual', 'Formal', 'Sports', 'Party', 'Ethnic', 'Western'];
        foreach ($styles as $index => $style) {
            AttributeValue::create([
                'attribute_id' => $styleAttribute->id,
                'value' => $style,
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        echo "✅ Created " . Attribute::count() . " attributes with " . AttributeValue::count() . " attribute values\n";
    }
}

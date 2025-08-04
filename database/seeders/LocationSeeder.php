<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create India
        $india = Country::create([
            'name' => 'India',
            'code' => 'IN',
            'phone_code' => '+91',
            'is_active' => true
        ]);

        // Create USA
        $usa = Country::create([
            'name' => 'United States',
            'code' => 'US',
            'phone_code' => '+1',
            'is_active' => true
        ]);

        // Indian States
        $states = [
            ['name' => 'Gujarat', 'code' => 'GJ'],
            ['name' => 'Maharashtra', 'code' => 'MH'],
            ['name' => 'Karnataka', 'code' => 'KA'],
            ['name' => 'Tamil Nadu', 'code' => 'TN'],
            ['name' => 'Delhi', 'code' => 'DL'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP'],
            ['name' => 'West Bengal', 'code' => 'WB'],
            ['name' => 'Rajasthan', 'code' => 'RJ'],
            ['name' => 'Punjab', 'code' => 'PB'],
            ['name' => 'Haryana', 'code' => 'HR'],
        ];

        foreach ($states as $stateData) {
            $state = State::create([
                'country_id' => $india->id,
                'name' => $stateData['name'],
                'code' => $stateData['code'],
                'is_active' => true
            ]);

            // Add cities for each state
            $this->createCitiesForState($state);
        }

        // US States (sample)
        $usStates = [
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'Texas', 'code' => 'TX'],
            ['name' => 'Florida', 'code' => 'FL'],
        ];

        foreach ($usStates as $stateData) {
            $state = State::create([
                'country_id' => $usa->id,
                'name' => $stateData['name'],
                'code' => $stateData['code'],
                'is_active' => true
            ]);

            $this->createUSCitiesForState($state);
        }
    }

    private function createCitiesForState($state)
    {
        $citiesData = [
            'Gujarat' => [
                ['name' => 'Ahmedabad', 'postal_code_prefix' => '380'],
                ['name' => 'Surat', 'postal_code_prefix' => '395'],
                ['name' => 'Vadodara', 'postal_code_prefix' => '390'],
                ['name' => 'Rajkot', 'postal_code_prefix' => '360'],
                ['name' => 'Gandhinagar', 'postal_code_prefix' => '382'],
            ],
            'Maharashtra' => [
                ['name' => 'Mumbai', 'postal_code_prefix' => '400'],
                ['name' => 'Pune', 'postal_code_prefix' => '411'],
                ['name' => 'Nashik', 'postal_code_prefix' => '422'],
                ['name' => 'Nagpur', 'postal_code_prefix' => '440'],
                ['name' => 'Thane', 'postal_code_prefix' => '400'],
            ],
            'Karnataka' => [
                ['name' => 'Bangalore', 'postal_code_prefix' => '560'],
                ['name' => 'Mysore', 'postal_code_prefix' => '570'],
                ['name' => 'Mangalore', 'postal_code_prefix' => '575'],
                ['name' => 'Hubli', 'postal_code_prefix' => '580'],
            ],
            'Tamil Nadu' => [
                ['name' => 'Chennai', 'postal_code_prefix' => '600'],
                ['name' => 'Coimbatore', 'postal_code_prefix' => '641'],
                ['name' => 'Madurai', 'postal_code_prefix' => '625'],
                ['name' => 'Salem', 'postal_code_prefix' => '636'],
            ],
            'Delhi' => [
                ['name' => 'New Delhi', 'postal_code_prefix' => '110'],
                ['name' => 'South Delhi', 'postal_code_prefix' => '110'],
                ['name' => 'North Delhi', 'postal_code_prefix' => '110'],
                ['name' => 'East Delhi', 'postal_code_prefix' => '110'],
            ],
            'Uttar Pradesh' => [
                ['name' => 'Lucknow', 'postal_code_prefix' => '226'],
                ['name' => 'Kanpur', 'postal_code_prefix' => '208'],
                ['name' => 'Agra', 'postal_code_prefix' => '282'],
                ['name' => 'Varanasi', 'postal_code_prefix' => '221'],
            ],
            'West Bengal' => [
                ['name' => 'Kolkata', 'postal_code_prefix' => '700'],
                ['name' => 'Howrah', 'postal_code_prefix' => '711'],
                ['name' => 'Durgapur', 'postal_code_prefix' => '713'],
            ],
            'Rajasthan' => [
                ['name' => 'Jaipur', 'postal_code_prefix' => '302'],
                ['name' => 'Jodhpur', 'postal_code_prefix' => '342'],
                ['name' => 'Udaipur', 'postal_code_prefix' => '313'],
            ],
            'Punjab' => [
                ['name' => 'Chandigarh', 'postal_code_prefix' => '160'],
                ['name' => 'Ludhiana', 'postal_code_prefix' => '141'],
                ['name' => 'Amritsar', 'postal_code_prefix' => '143'],
            ],
            'Haryana' => [
                ['name' => 'Gurgaon', 'postal_code_prefix' => '122'],
                ['name' => 'Faridabad', 'postal_code_prefix' => '121'],
                ['name' => 'Panipat', 'postal_code_prefix' => '132'],
            ],
        ];

        $cities = $citiesData[$state->name] ?? [];
        
        foreach ($cities as $cityData) {
            City::create([
                'state_id' => $state->id,
                'name' => $cityData['name'],
                'postal_code_prefix' => $cityData['postal_code_prefix'],
                'is_active' => true
            ]);
        }
    }

    private function createUSCitiesForState($state)
    {
        $citiesData = [
            'California' => [
                ['name' => 'Los Angeles', 'postal_code_prefix' => '900'],
                ['name' => 'San Francisco', 'postal_code_prefix' => '941'],
                ['name' => 'San Diego', 'postal_code_prefix' => '921'],
            ],
            'New York' => [
                ['name' => 'New York City', 'postal_code_prefix' => '100'],
                ['name' => 'Buffalo', 'postal_code_prefix' => '142'],
                ['name' => 'Rochester', 'postal_code_prefix' => '146'],
            ],
            'Texas' => [
                ['name' => 'Houston', 'postal_code_prefix' => '770'],
                ['name' => 'Austin', 'postal_code_prefix' => '787'],
                ['name' => 'Dallas', 'postal_code_prefix' => '752'],
            ],
            'Florida' => [
                ['name' => 'Miami', 'postal_code_prefix' => '331'],
                ['name' => 'Orlando', 'postal_code_prefix' => '328'],
                ['name' => 'Tampa', 'postal_code_prefix' => '336'],
            ],
        ];

        $cities = $citiesData[$state->name] ?? [];
        
        foreach ($cities as $cityData) {
            City::create([
                'state_id' => $state->id,
                'name' => $cityData['name'],
                'postal_code_prefix' => $cityData['postal_code_prefix'],
                'is_active' => true
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'discount' => 10.00,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addMonths(3),
            ],
            [
                'code' => 'SAVE20',
                'discount' => 20.00,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addMonths(2),
            ],
            [
                'code' => 'NEWUSER15',
                'discount' => 15.00,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addMonth(),
            ],
            [
                'code' => 'SUMMER25',
                'discount' => 25.00,
                'valid_from' => Carbon::now()->addDays(10),
                'valid_to' => Carbon::now()->addMonths(4),
            ],
            [
                'code' => 'FLASH50',
                'discount' => 50.00,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addWeeks(2),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}

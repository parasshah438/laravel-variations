<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);
        
        $coupon = Coupon::where('code', $request->code)->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 400);
        }
        
        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon has expired'
            ], 400);
        }
        
        session(['applied_coupon_code' => $coupon->code]);
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'discount' => $coupon->discount,
            'code' => $coupon->code
        ]);
    }
    
    public function remove()
    {
        session()->forget('applied_coupon_code');
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully'
        ]);
    }
}

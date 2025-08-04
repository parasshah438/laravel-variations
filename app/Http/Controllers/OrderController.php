<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\UserAddress;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{

    public function index()
    {
        $orders = Order::with('items.productVariation.product')
                      ->where('user_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return view('orders.index', compact('orders'));
    }
    
    public function show($id)
    {
        $order = Order::with([
            'items.productVariation.product.images',
            'items.productVariation.attributeValues.attribute',
            'user'
        ])->where('user_id', Auth::id())
          ->findOrFail($id);
        
        return view('orders.show', compact('order'));
    }
    
    public function downloadInvoice($id)
    {
        $order = Order::with([
            'items.productVariation.product.images',
            'items.productVariation.attributeValues.attribute',
            'user'
        ])->where('user_id', Auth::id())
          ->findOrFail($id);
        
        try {
            $pdf = Pdf::loadView('orders.invoice', compact('order'));
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download('invoice-' . $order->id . '.pdf');
        } catch (\Exception $e) {
            // Fallback: Return HTML view for now
            return view('orders.invoice', compact('order'))
                ->header('Content-Type', 'text/html');
        }
    }
    
    public function checkout()
    {
        $cartItems = Cart::with(['productVariation.product.images'])
                        ->where('user_id', Auth::id())
                        ->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Get user addresses
        $addresses = Auth::user()->addresses()
                        ->with(['country', 'state', 'city'])
                        ->active()
                        ->orderBy('is_default_shipping', 'desc')
                        ->orderBy('is_default', 'desc')
                        ->get();

        // Get countries for new address form
        $countries = Country::active()->orderBy('name')->get();
        
        $subtotal = $cartItems->sum(function($item) {
            return $item->qty * $item->productVariation->price;
        });
        
        $discount = 0;
        $couponCode = session('applied_coupon_code');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discount = $coupon->discount;
            }
        }
        
        $total = $subtotal - $discount;
        
        return view('checkout.index', compact('cartItems', 'addresses', 'countries', 'subtotal', 'discount', 'total', 'couponCode'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cod',
            // Delivery options validation
            'delivery_speed' => 'required|in:standard,express,same_day',
            'delivery_date' => 'nullable|date|after:today|before:' . date('Y-m-d', strtotime('+30 days')),
            'delivery_time_slot' => 'nullable|in:morning,afternoon,evening,night',
            'delivery_instructions' => 'nullable|string|max:500',
            // Gift options validation
            'is_gift' => 'nullable|boolean',
            'gift_wrap' => 'nullable|boolean',
            'gift_message' => 'nullable|string|max:300',
            'gift_recipient_name' => 'nullable|string|max:255',
            // Communication preferences validation
            'sms_updates' => 'nullable|boolean',
            'email_updates' => 'nullable|boolean'
        ]);

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->address_id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();
        
        $cartItems = Cart::with('productVariation')
                        ->where('user_id', Auth::id())
                        ->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }
        
        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->productVariation->stock < $item->qty) {
                return back()->with('error', 'Insufficient stock for ' . $item->productVariation->product->name);
            }
        }
        
        $subtotal = $cartItems->sum(function($item) {
            return $item->qty * $item->productVariation->price;
        });
        
        $discount = 0;
        $couponCode = session('applied_coupon_code');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discount = $coupon->discount;
            }
        }
        
        // Calculate delivery charges
        $deliveryCharge = match($request->delivery_speed) {
            'express' => 99.00,
            'same_day' => 199.00,
            default => 0.00
        };
        
        // Calculate gift wrap charge
        $giftWrapCharge = $request->gift_wrap ? 50.00 : 0.00;
        
        $total = $subtotal - $discount + $deliveryCharge + $giftWrapCharge;
        
        DB::transaction(function() use ($request, $cartItems, $total, $address, $deliveryCharge, $giftWrapCharge) {
            // Create order with all details
            $order = Order::create([
                'user_id' => Auth::id(),
                'address' => $address->formatted_address,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                // Delivery options
                'delivery_speed' => $request->delivery_speed,
                'delivery_date' => $request->delivery_date,
                'delivery_time_slot' => $request->delivery_time_slot,
                'delivery_instructions' => $request->delivery_instructions,
                // Gift options
                'is_gift' => $request->boolean('is_gift'),
                'gift_wrap' => $request->boolean('gift_wrap'),
                'gift_message' => $request->gift_message,
                'gift_recipient_name' => $request->gift_recipient_name,
                // Communication preferences
                'sms_updates' => $request->boolean('sms_updates', true),
                'email_updates' => $request->boolean('email_updates', true),
                // Additional charges
                'delivery_charge' => $deliveryCharge,
                'gift_wrap_charge' => $giftWrapCharge
            ]);
            
            // Create order items and update stock
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variation_id' => $item->product_variation_id,
                    'qty' => $item->qty,
                    'price' => $item->productVariation->price
                ]);
                
                // Update stock
                $item->productVariation->decrement('stock', $item->qty);
            }
            
            // Clear cart
            Cart::where('user_id', Auth::id())->delete();
            
            // Clear coupon session
            session()->forget('applied_coupon_code');
        });
        
        return redirect()->route('orders.index')->with('success', 'Order placed successfully!');
    }
}

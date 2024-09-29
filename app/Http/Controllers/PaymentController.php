<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function checkout()
    {
        $user = Auth::user();

        $cart = $user->carts()->latest()->first();

        if (!$cart) {
            return response()->json(['message' => 'No active cart available for checkout'], 404);
        }

        $gets = $user->cartItems()->get();
        $price = 0;
        foreach ($gets as $get) {
            $price += $get->total_price;
        };
        $order = Order::create([
            'user_id' => $user->id,
            'amount_paid' => $price,
            'status' => 'completed'
        ]);

        if ($order) {
            foreach ($gets as $item) {
                DB::table('order_item')->insert([
                    'order_id'     => $order->id,
                    'product_id'   => $item->id,
                    'quantity'     => $item->quantity,
                    'size'         => $item->size,
                    'retail_price' => $item->retail_price,
                    'total_price'  => $item->total_price,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
            $cart->products()->detach();

            $cart->delete();



            return response()->json('transaction ended completed');
        }
    }
}

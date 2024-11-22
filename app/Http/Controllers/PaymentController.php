<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $discountController;


    public function __construct(DiscountController $discountController)
    {
        $this->discountController = $discountController;
    }

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
            'status' => Status::Pending
        ]);
        $this->updatetotalspent($user, $price);
        $this->discountController->updateStatus($user);

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
                $i = $this->managequantity($item->id, $item->size, $item->quantity);
            }
            $cart->products()->detach();

            $cart->delete();


            return response()->json($i);
        }
    }

    public function updatetotalspent($user, $price)
    {
        $user->total_spent += $price;
        $user->save();
    }

    private function managequantity($id, $size, $quantity)
    {
        $product = Product::with('clothsize.quantities')->where('id', $id)->first();

        if ($product) {
            $clothsize = $product->clothsize->where('size', $size)->first();

            if ($clothsize) {
                $currentquantity = $clothsize->quantities()->first();

                if ($currentquantity && $currentquantity->quantity == 0) {
                    $clothsize->delete();
                }

                if ($currentquantity && $currentquantity->quantity >= $quantity) {
                    $currentquantity->update([
                        'quantity' => $currentquantity->quantity - $quantity
                    ]);

                    if ($currentquantity->quantity == 0) {
                        $clothsize->delete();
                    }

                    return "transaction ended succesfully";
                }
            }
            return 'Size not found';
        }
        return 'Product not found';
    }
}

<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function Like($id)
    {
        $user = Auth::user();

        if (!$user->manyproducts->contains($id)) {
            $user->manyproducts()->attach($id);
            return response()->json([
                'message' => 'liked',
            ]);
        } else {
            return response()->json([
                'message' => 'already like'
            ]);
        }
    }

    public function unLike($id)
    {
        $user = Auth::user();

        if ($user->manyproducts->contains($id)) {
            $user->manyproducts()->detach($id);
            return response()->json([
                'message' => 'unliked',
            ]);
        } else {
            return response()->json([
                'message' => 'already unlike'
            ]);
        }
    }

    public function addcart($id)
    {
        $user = Auth::user();
        $quantity = 1;
        $price = Product::where('user_id', $user->id)->where('id', $id)->value('price');
        $total_price = $price * $quantity;
        Cart::create([
            'total_price' => $total_price,
            'quantity' => $quantity,
            'product_id' => $id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'added',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
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

        $cart = Cart::where('user_id', $user->id)->where('product_id', $id)->first();
        if ($cart) {
            return response()->json(['message' => 'Already added'], 409);
        }

        $price = Product::where('user_id', '!=', $user->id)->where('id', $id)->value('price');
        if (is_null($price)) {
            return response()->json(['error' => 'Product not found or owned by the user'], 404);
        }

        $total_price = $price * 1;

        try {
            Cart::create([
                'total_price' => $total_price,
                'quantity' => 1,
                'product_id' => $id,
                'user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add product to cart'], 500);
        }
    }

    public function full()
    {
        $total = 0;
        $prices = Cart::where('user_id', Auth::user()->id)->pluck('total_price');
        foreach ($prices as $price) {
            $total += $price;
        }
        return response()->json([
            'total' => $total,
        ]);
    }
}

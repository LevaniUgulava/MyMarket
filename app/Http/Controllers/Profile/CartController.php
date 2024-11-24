<?php

namespace App\Http\Controllers\Profile;

use App\Enums\ProductSize;
use App\Helpers\ProductHelper;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

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

    public function count(Product $product)
    {
        $countcomment =  $product->commentusers()->count();
        return response()->json([
            'liked' => true,
            'countcomment' => $countcomment

        ]);
    }





    public function cart(Product $product, Request $request)
    {

        $user = Auth::user();

        $existingCart = $user->carts()->latest()->first();

        if (!$existingCart) {
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);
        } else {
            $cart = $existingCart;
        }

        $productexistsizes = $product->clothsize;
        $existsize = $productexistsizes->pluck('size')->toArray();
        $randomSize = $existsize[array_rand($existsize)];

        $cartItem = $cart->products()->where('product_id', $product->id)->where('size', $request->size)->first();
        $quantity =  1;
        $size = $request->size ? $request->size : $randomSize;
        $totalPrice = $user->getPriceByStatus($product, $product->price, $product->discountprice) * $quantity;
        if ($cartItem) {
            return response()->json(['message' => 'Product added Already']);
        } else {

            $cart->products()->attach($product->id, [
                'quantity' => $quantity,
                'size' => $size,
                'retail_price' => $user->getPriceByStatus($product, $product->price, $product->discountprice),
                'total_price' => $totalPrice
            ]);
            return response()->json(['message' => 'Product added to cart successfully']);
        }
    }


    public function getcart()
    {
        $user = Auth::user();

        $products = $user->cartItems()->get()->map(function ($product) use ($user) {
            $productModel = Product::find($product->id);

            $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                return  url('storage/' . $media->id . '/' . $media->file_name);
            });

            $product->price = $user->getPriceByStatus($productModel, $product->price, $product->discountprice);


            return $product;
        });

        $totalPrice = $products->sum('total_price');

        return response()->json(["products" => $products, 'totalPrice' => $totalPrice]);
    }





    public function updatequantity($id, $action, Request $request, ProductService $productService)
    {


        $user = Auth::user();
        $data = $user->cartItems()->where('product_id', $id)->where('size', $request->size)->first();

        $product = Product::with('clothsize.quantities')->where('id', $id)->first();

        $fullquantity = $productService->getClothsize($product, $request->size);

        $cart = $user->carts()->first();
        if ($data) {
            $currentquantity = $data->quantity;

            if ($action === 'increment' && $fullquantity > $currentquantity) {
                $currentquantity++;
            } elseif ($action === 'decrement' && $currentquantity > 1) {
                $currentquantity--;
            } else {
                return response()->json(['error' => 'Invalid action or quantity'], 400);
            }

            $newTotalPrice = $user->getPriceByStatus($product, $product->price, $product->discountprice) * $currentquantity;

            $updated = DB::table('cart_item')
                ->where('cart_id', $cart->id)
                ->where('product_id', $id)
                ->where('size', $request->size)
                ->update([
                    'quantity' => $currentquantity,
                    'total_price' => $newTotalPrice
                ]);

            if ($updated) {
                return response()->json(['message' => 'Quantity updated successfully']);
            } else {
                return response()->json(['error' => 'Failed to update quantity'], 500);
            }
        } else {
            return response()->json(['error' => 'Product not found in user orders'], 404);
        }
    }




    public function deletecart($id, Request $request)
    {
        $user = Auth::user();

        $cart = $user->carts()->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cartItem = DB::table('cart_item')
            ->where('cart_id', $cart->id)
            ->where('product_id', $id)
            ->where('size', $request->size)
            ->first();

        if (!$cartItem) {
            return response()->json(['error' => 'Product not found in cart'], 404);
        }

        $deleted = DB::table('cart_item')
            ->where('cart_id', $cart->id)
            ->where('product_id', $id)
            ->where('size', $request->size)
            ->delete();

        if ($deleted) {
            return response()->json(["message" => "Product deleted from cart"]);
        } else {
            return response()->json(["error" => "Failed to delete product"], 500);
        }
    }
}

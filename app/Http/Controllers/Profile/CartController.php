<?php

namespace App\Http\Controllers\Profile;

use App\Enums\ProductSize;
use App\Helpers\ProductHelper;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Clothsize;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shoessize;
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


        $randomSize = $this->randSizeHandle($product, $request->type);
        $randomColor = $this->randColorHandle($randomSize, $product->id);

        $cartItem = $cart->products()->where('product_id', $product->id)->where('size', $request->size)->first();
        $quantity = $request->quantity ?? 1;
        $size = $request->size ? $request->size : $randomSize;
        $color = $request->color ? $request->color : $randomColor;
        $totalPrice = $user->getPriceByStatus($product, $product->price, $product->discountprice) * $quantity;
        if ($cartItem) {
            return response()->json([
                'message' => 'პროდუქტი კალათაში დამატებულია'
            ]);
        } else {

            $cart->products()->attach($product->id, [
                'quantity' => $quantity,
                'size' => $size,
                "color" => $color,
                'retail_price' => $user->getPriceByStatus($product, $product->price, $product->discountprice),
                'total_price' => $totalPrice
            ]);
            return response()->json([
                'message' => 'პროდუქტი კალათაში დაემტა'
            ]);
        }
    }
    public function changeSize($id, Request $request)
    {
        $user = Auth::user();
        $currentsize = $request->size;
        $newsize = $request->newsize;
        $cart = $user->carts()->pluck("id");
        $updated = DB::table('cart_item')
            ->where('cart_id', $cart)
            ->where('product_id', $id)
            ->where('size', $currentsize)
            ->update([
                "size" => $newsize,
                "color" => null
            ]);

        if ($updated) {
            return response()->json(['message' => 'size updated successfully']);
        } else {
            return response()->json(['error' => 'Failed to update quantity'], 500);
        }
    }
    public function changeColor($id, Request $request)
    {
        $user = Auth::user();
        $currentsize = $request->size;
        $color = $request->color;
        $cart = $user->carts()->pluck("id");
        $updated = DB::table('cart_item')
            ->where('cart_id', $cart)
            ->where('product_id', $id)
            ->where('size', $currentsize)
            ->update([
                "color" => $color
            ]);

        if ($updated) {
            return response()->json(['message' => 'size updated successfully']);
        } else {
            return response()->json(['error' => 'Failed to update quantity'], 500);
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

            $rawdata = Color::whereHasMorph(
                "colorable",
                [Shoessize::class, Clothsize::class],
                function ($query) use ($product) {
                    $query->where("product_id", $product->id);
                }
            )
                ->with([
                    "colorable" => function ($query) {
                        $query->select("id", "size", "product_id");
                    }
                ])
                ->select("id", "color", "colorable_id", "colorable_type")
                ->get();

            $product->availablesize = $rawdata->groupby(function ($item) {
                return $item->colorable->size;
            })->map(function ($colors) {
                return $colors->pluck("color");
            });




            return $product;
        });


        $totalPrice = $products->sum('total_price');

        return response()->json(["products" => $products, 'totalPrice' => $totalPrice]);
    }





    public function updatequantity($id, $action, Request $request, ProductService $productService)
    {

        $user = Auth::user();
        $data = $user->cartItems()->where('product_id', $id)->where('size', $request->size)->first();

        $product = Product::where('id', $id)->first();

        $fullquantity = $productService->getSize($product, $request->size);

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

    private function randSizeHandle($product, $type)
    {
        $productexistsizes = $product->$type;
        $existsize = $productexistsizes->pluck('size')->toArray();
        return $existsize[array_rand($existsize)];
    }

    private function randColorHandle($size, $id)
    {
        $colors = Color::wherehasmorph("colorable", [Shoessize::class, Clothsize::class], function ($q) use ($size, $id) {
            $q->where("size", $size)->where("product_id", $id);
        })->pluck("color")->toArray();

        return $colors[array_rand($colors)];
    }
}

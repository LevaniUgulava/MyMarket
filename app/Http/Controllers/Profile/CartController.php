<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Pivot;
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

    public function count(Product $product)
    {
        $countcomment =  $product->commentusers()->count();
        $countlike = $product->users()->count();
        return response()->json([
            'countlike' => $countlike,
            'liked' => true,
            'countcomment' => $countcomment

        ]);
    }

    public function cart(Product $product)
    {

        $quantity =  1;
        $totalPrice = $product->discountprice * $quantity;

        $user = Auth::user();
        if (!$user->orderproduct()->where("product_id", $product->id)->exists()) {
            $user->orderproduct()->attach($product->id, [
                'retail_price' => $product->discountprice,
                'quantity' => $quantity,
                'total_price' => $totalPrice
            ]);
            return response()->json([
                'message' => 'Product added to cart successfully.'
            ]);
        } else {
            return response()->json(["message" => "alredy exist"]);
        }
    }

    public function getcart()
    {
        $user = Auth::user();
        $products = $user->orderproduct()->get()->map(function ($product) {
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name); // Ensure full URL is returned
            });
            return $product;
        });
        $allprice = 0;
        foreach ($products as $price) {
            $allprice += $price->pivot->total_price;
        }
        return response()->json(["product" => $products, "allprice" => $allprice]);
    }

    public function updatequantity($id, $action)
    {
        $user = Auth::user();
        $data = $user->orderproduct()->where('product_id', $id)->first();

        if ($data) {
            if ($action == 'increment') {
                $data->pivot->quantity++;
            } elseif ($action == 'decrement' && $data->pivot->quantity > 1) {
                $data->pivot->quantity--;
            }
            $data->pivot->total_price = $data->discountprice * $data->pivot->quantity;

            $data->pivot->save();



            return response()->json();
        } else {
            return response()->json(['error' => 'Product not found in user orders'], 404);
        }
    }

    public function deletecart($id)
    {
        $user = Auth::user();
        $user->orderproduct()->detach($id);

        return response()->json(["message" => "you deleted product"]);
    }
}

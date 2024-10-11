<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // public function myproducts()
    // {
    //     $products = Product::with('Maincategory', 'Category', 'Subcategory', 'Contacts', 'user')
    //         ->where('user_id', Auth::user()->id)->get();
    //     return ProductResource::collection($products);
    // }
    // public function getprofile()
    // {
    //     $user = Auth::user();
    //     return response()->json([
    //         'message' => $user,
    //     ]);
    // }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        return response()->json([
            'message' => 'user updated',
        ]);
    }
    public function getprofile()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);
    }
    public function likeproduct()
    {
        $user = Auth::user();
        $products = $user->manyproducts()->withAvg('rateproduct', 'rate')->get();
        foreach ($products as $product) {
            $product->isLiked = true;
        }
        return ProductResource::collection($products);
    }
}

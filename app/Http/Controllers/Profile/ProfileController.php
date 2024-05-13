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
    public function myproducts()
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'Contacts', 'user')
            ->where('user_id', Auth::user()->id)->get();
        return ProductResource::collection($products);
    }

    public function profile(RegisterRequest $request)
    {
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'message' => 'user updated',
        ]);
    }
}

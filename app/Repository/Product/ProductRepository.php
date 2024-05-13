<?php

namespace App\Repository\Product;

use App\Http\Resources\ProductResource;
use Illuminate\Support\Str;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductRepository implements ProductRepositoryInterface
{

    public function display()
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'Contacts', 'user')->get();
        return $products;
    }
    public function create(Request $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'maincategory_id' => $request->maincategory_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'user_id' => Auth::user()->id,
        ]);

        $product->Contacts()->create([
            'number' => $request->number,
            'product_id' => $product->id,
        ]);

        $product->addMediaFromRequest('images')->toMediaCollection();

        return true;
    }
}

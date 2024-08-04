<?php

namespace App\Repository\Product;

use App\Http\Resources\ProductResource;
use Illuminate\Support\Str;

use App\Models\Product;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{

    public function display($name, $maincategoryid, $pagination)
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'Contacts', 'user')
            ->where('active', 1)
            ->searchname($name)
            ->searchmain($maincategoryid)
            ->inRandomOrder()
            ->paginate($pagination);

        // Adding media URLs to each product
        $products->getCollection()->transform(function ($product) {
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return $media->getUrl();
            });
            return $product;
        });

        return $products;
    }



    public function isliked()
    {
        $user = auth()->user();
        $data = $user->manyproducts()->distinct()->get();
        return $data;
    }

    public function admindisplay($pagination)
    {
        $products = Product::with(['Maincategory', 'Category', 'Subcategory', 'Contacts', 'user'])
            ->paginate($pagination);

        // Map image URLs after pagination
        $products->getCollection()->transform(function ($product) {
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return $media->getUrl();
            });
            return $product;
        });

        return $products;
    }




    public function notactive($id)
    {
        $product = Product::findorfail($id);
        $product->update([
            'active' => 0,
        ]);
    }

    public function active($id)
    {
        $product = Product::findorfail($id);
        $product->update([
            'active' => 1,
        ]);
    }


    public function displaybyid($id)
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'Contacts')->where('id', $id)->get();
        return $products;
    }
    public function create(Request $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discountprice' => $request->price,
            'maincategory_id' => $request->maincategory_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
        ]);

        $product->Contacts()->create([
            'number' => $request->number,
            'product_id' => $product->id,
        ]);

        $product->addMultipleMediaFromRequest(['images'])
            ->each(function ($fileAdder) {
                $fileAdder->toMediaCollection();
            });


        return true;
    }
}

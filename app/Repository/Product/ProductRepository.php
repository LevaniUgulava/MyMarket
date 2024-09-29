<?php

namespace App\Repository\Product;

use App\Enums\ProductSize;
use App\Http\Requests\CreateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Clothsize;
use Illuminate\Support\Str;

use App\Models\Product;
use App\Models\Shoessize;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{

    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user)
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'user', 'clothsize.quantities', 'shoesize.quantities')
            ->where('active', 1)
            ->searchname($name)
            ->searchmain($maincategoryid)
            ->searchcategory($categoryid)
            ->searchcategory($categoryid)
            ->searchsubcategory($subcategoryid)
            ->paginate($pagination);

       
        $products->getCollection()->transform(function ($product) use ($user) {
            // Add image URLs from the media library
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return $media->getUrl();
            });

            $product->isLiked = $user ? $user->manyproducts()->where('product_id', $product->id)->exists() : false;
            return $product;
        });

        return $products;
    }



    public function admindisplay($pagination)
    {
        $products = Product::with(['Maincategory', 'Category', 'Subcategory', 'user'])
            ->paginate($pagination);

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
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'shoesize', 'clothsize')->where('id', $id)->get();
        return $products;
    }
    public function create(Request $request)
    {

        try {

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'discountprice' => $request->price,
                'maincategory_id' => $request->maincategory_id,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
            ]);

            $sizes = $request->input('size', []);
            $quantities = $request->input('quantity', []);
            if ($request->size_type === "numeric") {
                foreach ($sizes as $index => $size) {
                    $numeric_size = Shoessize::create([
                        'size' => $size,
                        'product_id' => $product->id,
                    ]);
                    $numeric_size->quantities()->create([
                        'quantity' => $quantities[$index],
                    ]);
                }
            } elseif ($request->size_type === "letter-based") {

                foreach ($sizes as $index => $size) {
                    $letterbased = Clothsize::create([
                        'size' => $size,
                        'product_id' => $product->id,
                    ]);
                    $letterbased->quantities()->create([
                        'quantity' => $quantities[$index],
                    ]);
                }
            }



            $product->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection();
                });


            return true;
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());
            return  false;
        }
    }
}

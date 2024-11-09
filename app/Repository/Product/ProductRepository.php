<?php

namespace App\Repository\Product;

use App\Enums\ProductSize;
use App\Helpers\Translator;
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
use LaravelLang\Translator\Facades\Translate;

class ProductRepository implements ProductRepositoryInterface
{

    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user, $section, $lang)
    {

        $products = Product::withAvg('rateproduct', 'rate')
            ->with(['Maincategory', 'Category', 'Subcategory'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryid, fn($query) => $query->searchmain($maincategoryid))
            ->when($categoryid, fn($query) => $query->searchcategory($categoryid))
            ->when($subcategoryid, fn($query) => $query->searchsubcategory($subcategoryid))
            ->when($section, fn($query) => $query->section($section))
            ->where('active', 1)
            ->paginate($pagination);


        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $ratedProductIds = $user ? $user->rateuser()->pluck('product_id')->toArray() : [];

        $products->getCollection()->transform(function ($product) use ($likedProductIds, $ratedProductIds, $lang) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);
            $product->isRated = in_array($product->id, $ratedProductIds);

            $cacheKeyDesc = "product_{$product->id}_description_{$lang}";

            $product->description = Cache::remember($cacheKeyDesc, 60 * 60 * 24, fn() => Translator::translate($product->description, $lang));

            return $product;
        });

        return $products;
    }



    public function admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination)
    {
        $products = Product::with(['Maincategory', 'Category', 'Subcategory', 'user'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryid, fn($query) => $query->searchmain($maincategoryid))
            ->when($categoryid, fn($query) => $query->searchcategory($categoryid))
            ->when($subcategoryid, fn($query) => $query->searchsubcategory($subcategoryid))
            ->paginate($pagination);;

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


    public function displaybyid($id, $user)
    {
        $products = Product::with('Maincategory', 'Category', 'Subcategory', 'shoesize', 'clothsize')
            ->withAvg('rateproduct', 'rate')
            ->where('id', $id)->get();

        $products = $products->map(function ($product) use ($user) {

            $product->isLiked = $user ? $user->manyproducts()->where('product_id', $product->id)->exists() : false;
            $product->isRated = $user ? $user->rateuser()->where('product_id', $product->id)->exists() : false;
            if ($product->isRated) {
                $product->MyRate = $user->rateuser()->where('product_id', $product->id)->first()->rate;
            }
            $product->name = Translator::translate($product->name, "ka");
            $product->description = Translator::translate($product->description, "ka");

            return $product;
        });

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
            dd($e->getMessage());
            return  false;
        }
    }
}

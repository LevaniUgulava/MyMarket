<?php

namespace App\Repository\Product;

use App\Helpers\ProductHelper;
use App\Helpers\Translator;
use App\Models\Clothsize;
use App\Models\Product;
use App\Models\Shoessize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{
    private $productHelper;
    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
    }
    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user, $section, $lang, $price1, $price2)
    {
        $products = Product::query()->withAvg('rateproduct', 'rate')
            ->with(['Maincategory:id,name', 'Category:id,name', 'Subcategory:id,name', 'eligibleStatuses'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryid, fn($query) => $query->searchmain($maincategoryid))
            ->when($categoryid, fn($query) => $query->searchcategory($categoryid))
            ->when($subcategoryid, fn($query) => $query->searchsubcategory($subcategoryid))
            ->when($section, fn($query) => $query->section($user, $section))
            ->when($price1 || $price2, fn($query) => $query->price($price1, $price2))
            ->where('active', 1)
            ->paginate($pagination);

        $this->productHelper->transform($products, $user, $lang, false);

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
        $product = Product::with('shoesize', 'clothsize')
            ->withAvg('rateproduct', 'rate')
            ->where('id', $id)
            ->first();
        $this->productHelper->transformSingleProduct($product, $user);

        return $product;
    }
    public function similarproducts($id, $user)
    {
        $product = Product::where('id', $id)->first();
        $categoryid = $product->category_id;
        $products = Product::where('id', '!=', '$id')
            ->where('category_id', $categoryid)
            ->withAvg('rateproduct', 'rate')
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $this->productHelper->transform($products, $user, null, false);
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

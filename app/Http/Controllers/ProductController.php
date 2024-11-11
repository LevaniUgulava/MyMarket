<?php

namespace App\Http\Controllers;

use App\Enums\ProductSize;
use App\Helpers\EnumHelper;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DiscountNotification;
use App\Notifications\RegisterNotification;
use App\Repository\Product\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use \App\Helpers;
use App\Http\Requests\CreateProductRequest;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Gd\Commands\GetSizeCommand;

class ProductController extends Controller
{
    public $productRepository;
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function display(Request $request)
    {
        $user = auth('sanctum')->user();
        $name = $request->query('searchname', '');
        $maincategoryid = $request->query('maincategory', '');
        $categoryid = $request->query('category', '');
        $subcategoryid = $request->query('subcategory', '');
        $section = (array)$request->query('section', []);
        $lang = $request->query('lang', '');
        $price1 = $request->query('min', '');
        $price2 = $request->query('max', '');

        $pagination = $request->get('perPage', 25);

        $result = [];
        foreach ($section as $s) {
            $products = $this->productRepository->display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user, $s, $lang, $price1, $price2);
            $result[$s] = ProductResource::collection($products);
        }
        return response()->json($result);
    }



    public function admindisplay(Request $request)
    {
        $name = $request->query('searchname', '');
        $maincategoryid = $request->query('maincategory', '');
        $categoryid = $request->query('category', '');
        $subcategoryid = $request->query('subcategory', '');
        $pagination = $request->query('perPage', 12);

        $products = $this->productRepository->admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination);
        return ProductResource::collection($products);
    }

    public function notactive($id)
    {
        $products = $this->productRepository->notactive($id);
        return response()->json(['message' => 'yes']);
    }
    public function active($id)
    {
        $products = $this->productRepository->active($id);
        return response()->json(['message' => 'yes']);
    }

    public function displaybyid($id)
    {
        $user = auth('sanctum')->user();
        $products = $this->productRepository->displaybyid($id, $user);
        return ProductResource::collection($products);
    }

    public function create(Request $request)
    {
        try {
            $check = $this->productRepository->create($request);

            if (!$check) {
                return response()->json([
                    'message' => 'Something went wrong!!',
                ], 500);
            }

            return response()->json([
                'message' => 'Added Successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function discount(Request $request)
    {

        $ids = $request->id;
        $discount = $request->discount;


        foreach ($ids as $id) {
            $product = Product::findOrFail($id);
            $price = $product->price;
            $product->update([
                "discount" => $discount,
                "discountprice" => $price * ((100 - $discount) / 100)
            ]);
        }
        if ($request->send) {
            $users = User::get();
            foreach ($users as $user) {
                $user->notify(new DiscountNotification($ids, $user->name, $discount));
            }
            $this->discountproducts($ids);
        }

        return response()->json("success");
    }
    public function discountproducts($ids)
    {
        if (empty($ids)) {
            return response()->json("No discounted products found", 404);
        }

        $products = Product::whereIn('id', $ids)->get();

        return response()->json($products);
    }

    public function getSizes()
    {
        return response()->json([
            "sizes" => EnumHelper::GetSizesAsArray(),
        ]);
    }
}

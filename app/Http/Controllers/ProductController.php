<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repository\Product\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public $productRepository;
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function display(Request $request)
    {
        $name = $request->query('searchname', '');
        $maincategoryid = $request->query('maincategory', '');
        $pagination = $request->get('perPage', 16);

        $products = $this->productRepository->display($name, $maincategoryid, $pagination);
        return ProductResource::collection($products);
    }


    public function isliked(Product $product)
    {
        $data = $this->productRepository->isliked($product);
        return response()->json($data);
    }
    public function admindisplay(Request $request)
    {
        $pagination = $request->query('perPage', 8); // Default to 8 if 'perPage' is not provided
        $products = $this->productRepository->admindisplay($pagination);
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
        $products = $this->productRepository->displaybyid($id);
        return ProductResource::collection($products);
    }

    public function create(Request $request)
    {
        $check = $this->productRepository->create($request);
        if (!$check) {
            return response()->json([
                'message' => 'Something went wrong!!',
            ], 500);
        }

        return response()->json([
            'message' => 'added Succesfully',
        ], 201);
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

        return response()->json("success");
    }
}

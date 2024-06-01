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

    public function display()
    {
        $products = $this->productRepository->display();
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

    public function filterbyname(Request $request)
    {
        $name = '%' . $request->name . '%';
        $products = Product::where('name', 'LIKE', $name)->get();
        return ProductResource::collection($products);
    }

    public function filterbycategory(Request $request)
    {
        $products = Product::where('maincategory_id', $request->id)->get();
        return ProductResource::collection($products);
    }
}

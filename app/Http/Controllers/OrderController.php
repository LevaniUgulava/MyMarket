<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Node\FunctionNode;

class OrderController extends Controller
{

    public function getorder()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('products')->get()->map(function ($order) {
            $order->products = $order->products->map(function ($product) {
                $productModel = Product::find($product->id);

                $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                });

                return $product;
            });

            return $order;
        });
        $groupedOrders = [];
        $groupedStatus = [];
        foreach ($orders as $order) {
            $products = [];

            foreach ($order->products as $product) {
                $products[] = [
                    'name' => $product->name,
                    'quantity'     => $product->pivot->quantity,
                    'size'         => $product->pivot->size,
                    'retail_price' => $product->pivot->retail_price,
                    'total_price'  => $product->pivot->total_price,
                    'image_urls' => $product->image_urls
                ];
            }

            $groupedOrders[] = [
                'order_id' => $order->id,
                'order_amount' => $order->amount_paid,
                'order_status' => $order->status,
                'products' => $products
            ];

            $groupedStatus[$order->status][] = end($groupedOrders);
        }

        return response()->json($groupedStatus);
    }

    public function getadminorder()
    {
        $orders = Order::with('user')->get()->map(function ($order) {

            $order->products = $order->products->map(function ($product) {
                $productModel = Product::find($product->id);

                $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                });

                return $product;
            });
            return $order;
        });

        $groupedOrders = [];
        foreach ($orders as $order) {
            $products = [];

            foreach ($order->products as $product) {
                $products[] = [
                    'name' => $product->name,
                    'quantity'     => $product->pivot->quantity,
                    'size'         => $product->pivot->size,
                    'retail_price' => $product->pivot->retail_price,
                    'total_price'  => $product->pivot->total_price,
                    'image_urls' => $product->image_urls
                ];
            }
            $groupedOrders[] = [
                'order_id' => $order->id,
                'order_amount' => $order->amount_paid,
                'order_status' => $order->status,
                'products' => $products,
                'user' => $order->user,

            ];
        }

        return response()->json($groupedOrders);
    }
}

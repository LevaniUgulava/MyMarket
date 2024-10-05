<?php

namespace App\Services;

class ProductService
{
    public function getClothsize($product, $size)
    {
        if ($product) {
            $clothsize = $product->clothsize->where('size', $size)->first();

            if ($clothsize) {
                $fullquantity = $clothsize->quantities()->first()->quantity;
                return $fullquantity;
            }
        }
    }
}

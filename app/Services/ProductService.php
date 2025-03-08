<?php

namespace App\Services;

class ProductService
{
    public function getSize($product, $size)
    {
        if ($product) {
            $clothsize = $product->clothsize->where('size', $size)->first();
            $shoesize = $product->shoesize->where('size', $size)->first();
            $size = $clothsize ?? $shoesize;
            if ($size) {
                $fullquantity = $size->colors()->first()->quantity;
                return $fullquantity;
            }
        }
    }
}

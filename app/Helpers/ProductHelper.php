<?php

namespace App\Helpers;


class ProductHelper
{

    public function transform($products, $user, $lang = null, $size = true)
    {
        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $products->transform(function ($product) use ($likedProductIds, $lang, $user, $size) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);

            if ($user) {
                $isEligible =  $product->eligibleStatuses()->wherePivot('userstatus_id', $user->userstatus->id)->withPivot('discount')->first();
                if ($isEligible && $user->userstatus->isActive()) {
                    $product->discountstatus = [
                        // 'userstatus' => $user->userstatus,
                        'discount' => $isEligible->pivot->discount
                    ];
                    $product->discountprice = $user->getPriceByStatus($product, $product->price, $product->price);
                } else {
                    $product->discountprice = $product->discountprice;
                }
            } else {
                $product->discountprice = $product->discountprice;
            }

            if ($size) {
                if ($product->shoesize->isNotEmpty()) {
                    $product->size_type = "numericbased";
                } elseif ($product->clothsize->isNotEmpty()) {
                    $product->size_type = "letterbased";
                }
            }
            unset($product->media);


            return $product;
        });
    }
}

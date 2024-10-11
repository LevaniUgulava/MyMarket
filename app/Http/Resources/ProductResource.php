<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Ramsey\Uuid\Type\Decimal;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'size' => $this->clothsize->isNotEmpty()
                ? $this->clothsize->map(function ($size) {
                    return [
                        'size' => $size->size,
                        'quantity' => $size->quantities->isNotEmpty() ? $size->quantities->first()->quantity : null
                    ];
                })
                : ($this->shoesize->isNotEmpty()
                    ? $this->shoesize->map(function ($size) {
                        return [
                            'size' => $size->size,
                            'quantity' => $size->quantities->isNotEmpty() ? $size->quantities->first()->quantity : null
                        ];
                    })
                    : null),
            'discount' => $this->discount,
            'discountprice' => $this->discountprice,
            'MainCategory' => $this->MainCategory->name,
            'Category' => $this->Category->name,
            'SubCategory' => $this->Subcategory->name,
            'image_urls' => $this->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            }),
            'active' => $this->active,
            'isLiked' => $this->isLiked ?? false,
            'isRated' => $this->isRated ?? false,
            'Rate' => number_format((float)$this->rateproduct_avg_rate, 1),
            'MyRate' => (float)$this->MyRate

        ];
    }
}
